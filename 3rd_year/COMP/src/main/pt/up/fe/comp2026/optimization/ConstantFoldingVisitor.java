package pt.up.fe.comp2026.optimization;

import pt.up.fe.comp.jmm.ast.AJmmVisitor;
import pt.up.fe.comp.jmm.ast.JmmNode;
import pt.up.fe.comp.jmm.ast.JmmNodeImpl;

import static pt.up.fe.comp2026.jmm.ast.JmmKind.*;

/**
 * AST visitor that folds constant expressions at compile time.
 */
public class ConstantFoldingVisitor extends AJmmVisitor<Void, Boolean> {

    @Override
    protected void buildVisitor() {
        addVisit(BINARY_EXPR, this::visitBinaryExpr);
        addVisit(UNARY_EXPR, this::visitUnaryExpr);
        addVisit(PAREN_EXPR, this::visitParenExpr);
        setDefaultVisit(this::defaultVisit);
    }

    public boolean fold(JmmNode root) {
        return visit(root) == Boolean.TRUE;
    }

    private Boolean defaultVisit(JmmNode node, Void unused) {
        boolean changed = false;
        for (int i = 0; i < node.getNumChildren(); i++) {
            if (visit(node.getChild(i)) == Boolean.TRUE) {
                changed = true;
                i--; // child may have been replaced, recheck
            }
        }
        return changed;
    }

    private Boolean visitParenExpr(JmmNode node, Void unused) {
        boolean changed = false;
        for (int i = 0; i < node.getNumChildren(); i++) {
            if (visit(node.getChild(i)) == Boolean.TRUE) {
                changed = true;
            }
        }
        // If inner expr is a literal, unwrap the paren
        if (node.getNumChildren() == 1) {
            var inner = node.getChild(0);
            if (INTEGER_LITERAL.check(inner) || BOOLEAN_LITERAL.check(inner)) {
                replaceWithNode(node, inner);
                return true;
            }
        }
        return changed;
    }

    private Boolean visitBinaryExpr(JmmNode node, Void unused) {
        // First fold children
        boolean changed = false;
        for (int i = 0; i < node.getNumChildren(); i++) {
            if (visit(node.getChild(i)) == Boolean.TRUE) {
                changed = true;
            }
        }

        var lhs = node.getChild(0);
        var rhs = node.getChild(1);
        var op = node.get("op");

        if (INTEGER_LITERAL.check(lhs) && INTEGER_LITERAL.check(rhs)) {
            int a = Integer.parseInt(lhs.get("value"));
            int b = Integer.parseInt(rhs.get("value"));

            switch (op) {
                case "+" -> { replaceWithInt(node, a + b); return true; }
                case "-" -> { replaceWithInt(node, a - b); return true; }
                case "*" -> { replaceWithInt(node, a * b); return true; }
                case "/" -> {
                    if (b != 0) { replaceWithInt(node, a / b); return true; }
                }
                case "%" -> {
                    if (b != 0) { replaceWithInt(node, a % b); return true; }
                }
                case "<" -> { replaceWithBool(node, a < b); return true; }
                case ">" -> { replaceWithBool(node, a > b); return true; }
                case "<=" -> { replaceWithBool(node, a <= b); return true; }
                case ">=" -> { replaceWithBool(node, a >= b); return true; }
                case "==" -> { replaceWithBool(node, a == b); return true; }
                case "!=" -> { replaceWithBool(node, a != b); return true; }
            }
        }

        if (BOOLEAN_LITERAL.check(lhs) && BOOLEAN_LITERAL.check(rhs)) {
            boolean a = lhs.get("value").equals("true");
            boolean b = rhs.get("value").equals("true");

            switch (op) {
                case "&&" -> { replaceWithBool(node, a && b); return true; }
                case "||" -> { replaceWithBool(node, a || b); return true; }
                case "==" -> { replaceWithBool(node, a == b); return true; }
                case "!=" -> { replaceWithBool(node, a != b); return true; }
            }
        }

        return changed;
    }

    private Boolean visitUnaryExpr(JmmNode node, Void unused) {
        boolean changed = false;
        for (int i = 0; i < node.getNumChildren(); i++) {
            if (visit(node.getChild(i)) == Boolean.TRUE) {
                changed = true;
            }
        }

        var operand = node.getChild(0);
        var op = node.get("op");

        if (INTEGER_LITERAL.check(operand)) {
            int val = Integer.parseInt(operand.get("value"));
            switch (op) {
                case "-" -> { replaceWithInt(node, -val); return true; }
                case "+" -> { replaceWithInt(node, val); return true; }
            }
        }

        if (BOOLEAN_LITERAL.check(operand)) {
            boolean val = operand.get("value").equals("true");
            if (op.equals("!")) {
                replaceWithBool(node, !val);
                return true;
            }
        }

        return changed;
    }

    private void replaceWithInt(JmmNode node, int value) {
        var literal = new JmmNodeImpl(INTEGER_LITERAL);
        literal.put("value", String.valueOf(value));
        copyLocation(node, literal);
        node.replace(literal);
    }

    private void replaceWithBool(JmmNode node, boolean value) {
        var literal = new JmmNodeImpl(BOOLEAN_LITERAL);
        literal.put("value", String.valueOf(value));
        copyLocation(node, literal);
        node.replace(literal);
    }

    private void replaceWithNode(JmmNode target, JmmNode replacement) {
        // Clone the replacement and replace target
        copyLocation(target, replacement);
        target.replace(replacement);
    }

    private void copyLocation(JmmNode source, JmmNode target) {
        for (var attr : java.util.List.of("lineStart", "colStart", "lineEnd", "colEnd")) {
            source.getOptionalObject(attr).ifPresent(v -> target.putObject(attr, v));
        }
    }
}
