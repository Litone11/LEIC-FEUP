package server;

import java.util.ArrayDeque;
import java.util.Deque;
import java.util.concurrent.locks.Condition;
import java.util.concurrent.locks.ReentrantLock;

final class OutboundQueue {
    private final int maxSize;
    private final Deque<String> frames = new ArrayDeque<>();
    private final ReentrantLock lock = new ReentrantLock();
    private final Condition notEmptyOrClosed = lock.newCondition();

    private boolean closed;

    OutboundQueue(int maxSize) {
        if (maxSize <= 0) {
            throw new IllegalArgumentException("maxSize must be positive");
        }
        this.maxSize = maxSize;
    }

    boolean offer(String frame) {
        lock.lock();
        try {
            if (closed) {
                return true;
            }
            if (frames.size() >= maxSize) {
                return false;
            }
            frames.addLast(frame);
            notEmptyOrClosed.signal();
            return true;
        } finally {
            lock.unlock();
        }
    }

    String take() throws InterruptedException {
        lock.lock();
        try {
            while (frames.isEmpty() && !closed) {
                notEmptyOrClosed.await();
            }
            if (!frames.isEmpty()) {
                return frames.removeFirst();
            }
            return null;
        } finally {
            lock.unlock();
        }
    }

    boolean closeAfter(String finalFrame) {
        lock.lock();
        try {
            if (closed) {
                return false;
            }
            frames.clear();
            frames.addLast(finalFrame);
            closed = true;
            notEmptyOrClosed.signalAll();
            return true;
        } finally {
            lock.unlock();
        }
    }

    void close() {
        lock.lock();
        try {
            closed = true;
            frames.clear();
            notEmptyOrClosed.signalAll();
        } finally {
            lock.unlock();
        }
    }
}
