package pt.up.fe.comp2026.ast;

import pt.up.fe.comp.jmm.ast.JmmNode;
import pt.up.fe.comp.jmm.ast.JmmNodeImpl;
import pt.up.fe.comp2026.jmm.ast.JmmKind;

import java.util.IdentityHashMap;
import java.util.List;
import java.util.Map;

public class NodeUtils {

    public static int getLine(JmmNode node) {

        return getIntegerAttribute(node, "lineStart", "-1");
    }

    public static int getColumn(JmmNode node) {

        return getIntegerAttribute(node, "colStart", "-1");
    }

    public static int getIntegerAttribute(JmmNode node, String attribute, String defaultVal) {
        String line = node.getOptional(attribute).orElse(defaultVal);
        return Integer.parseInt(line);
    }

    public static boolean getBooleanAttribute(JmmNode node, String attribute, String defaultVal) {
        String line = node.getOptional(attribute).orElse(defaultVal);
        return Boolean.parseBoolean(line);
    }

    public static JmmNode cloneSubtree(JmmNode node) {
        return cloneSubtree(node, new IdentityHashMap<>());
    }

    private static JmmNode cloneSubtree(JmmNode node, Map<JmmNode, JmmNode> clones) {
        var existingClone = clones.get(node);
        if (existingClone != null) {
            return existingClone;
        }

        var clone = new JmmNodeImpl(node.getKind());
        clones.put(node, clone);

        for (var attribute : node.getAttributes()) {
            clone.putObject(attribute, cloneAttribute(node.getObject(attribute), clones));
        }

        for (var child : node.getChildren()) {
            clone.add(cloneSubtree(child, clones));
        }

        return clone;
    }

    private static Object cloneAttribute(Object value, Map<JmmNode, JmmNode> clones) {
        if (value instanceof JmmNode childNode) {
            return cloneSubtree(childNode, clones);
        }

        if (value instanceof List<?> list) {
            return list.stream()
                    .map(element -> cloneAttribute(element, clones))
                    .toList();
        }

        return value;
    }


}
