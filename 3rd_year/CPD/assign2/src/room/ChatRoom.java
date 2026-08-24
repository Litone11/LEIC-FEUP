package room;

import protocol.Protocol;

import java.util.ArrayList;
import java.util.HashSet;
import java.util.List;
import java.util.Set;
import java.util.concurrent.locks.ReentrantReadWriteLock;

public final class ChatRoom {
    private static final int MAX_HISTORY = 100;

    private final String name;
    private final boolean preserveFullHistory;
    private final List<Message> history = new ArrayList<>();
    private final Set<RoomMember> members = new HashSet<>();
    private final ReentrantReadWriteLock lock = new ReentrantReadWriteLock();

    public ChatRoom(String name) {
        this(name, false);
    }

    public ChatRoom(String name, boolean preserveFullHistory) {
        this.name = name;
        this.preserveFullHistory = preserveFullHistory;
    }

    public String name() {
        return name;
    }

    public void join(RoomMember member) {
        join(member, Protocol.Command.JOINED);
    }

    public void join(RoomMember member, Protocol.Command confirmationCommand) {
        List<RoomMember> memberSnapshot;

        lock.writeLock().lock();
        try {
            members.add(member);
            memberSnapshot = new ArrayList<>(members);
        } finally {
            lock.writeLock().unlock();
        }

        member.send(confirmationCommand, name);

        broadcast(memberSnapshot, Protocol.encode(
                Protocol.Command.INFO,
                name,
                "[" + member.username() + " enters the room]"
        ));
    }

    public void leave(RoomMember member) {
        List<RoomMember> memberSnapshot;
        boolean wasMember;

        lock.writeLock().lock();
        try {
            memberSnapshot = new ArrayList<>(members);
            wasMember = members.remove(member);
        } finally {
            lock.writeLock().unlock();
        }

        if (wasMember) {
            broadcast(memberSnapshot, Protocol.encode(
                    Protocol.Command.INFO,
                    name,
                    "[" + member.username() + " leaves the room]"
            ));
            member.send(Protocol.Command.LEFT, name);
        }
    }

    public void addUserMessage(String username, String text) {
        Message message = new Message(username, text);
        List<RoomMember> memberSnapshot;

        lock.writeLock().lock();
        try {
            history.add(message);
            if (!preserveFullHistory && history.size() > MAX_HISTORY) {
                history.remove(0);
            }
            memberSnapshot = new ArrayList<>(members);
        } finally {
            lock.writeLock().unlock();
        }

        broadcast(memberSnapshot, Protocol.encode(
                Protocol.Command.MESSAGE,
                name,
                username,
                text
        ));
    }

    public String messageContext() {
        lock.readLock().lock();
        try {
            StringBuilder context = new StringBuilder();
            for (Message message : history) {
                context.append(message.username())
                        .append(": ")
                        .append(message.text())
                        .append(System.lineSeparator());
            }
            return context.toString();
        } finally {
            lock.readLock().unlock();
        }
    }

    private void broadcast(List<RoomMember> recipients, String line) {
        for (RoomMember recipient : recipients) {
            recipient.sendRaw(line);
        }
    }
}
