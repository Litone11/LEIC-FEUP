package pt.up.fe.comp2026.optimization;

import pt.up.fe.comp.jmm.ast.AJmmVisitor;
import pt.up.fe.comp.jmm.ast.JmmNode;
import pt.up.fe.comp.jmm.ast.JmmNodeImpl;
import pt.up.fe.comp2026.ast.NodeUtils;

import java.util.List;

import static pt.up.fe.comp2026.jmm.ast.JmmKind.*;

/**
 * Removes branches whose condition is known at compile time.
 */
public class BranchEliminationVisitor extends AJmmVisitor<Void, Boolean> {

    @Override
    protected void buildVisitor() {
        addVisit(IF_ELSE_STMT, this::visitIfElseStmt);
        addVisit(IF_STMT, this::visitIfStmt);
        addVisit(WHILE_STMT, this::visitWhileStmt);
        addVisit(DO_WHILE_STMT, this::visitDoWhileStmt);
        setDefaultVisit(this::defaultVisit);
    }

    public boolean eliminate(JmmNode root) {
        return visit(root) == Boolean.TRUE;
    }

    private Boolean defaultVisit(JmmNode node, Void unused) {
        boolean changed = false;
        for (int i = 0; i < node.getNumChildren(); i++) {
            if (visit(node.getChild(i)) == Boolean.TRUE) {
                changed = true;
                i--;
            }
        }
        return changed;
    }

    private Boolean visitIfElseStmt(JmmNode node, Void unused) {
        boolean changed = defaultVisit(node, unused);

        var condition = node.getChild(0);
        if (!BOOLEAN_LITERAL.check(condition)) {
            return changed;
        }

        var chosenBranch = condition.get("value").equals("true")
                ? deepCopy(node.getChild(1))
                : deepCopy(node.getChild(2));
        copyLocation(node, chosenBranch);
        node.replace(chosenBranch);
        return true;
    }

    private Boolean visitIfStmt(JmmNode node, Void unused) {
        boolean changed = defaultVisit(node, unused);

        var condition = node.getChild(0);
        if (!BOOLEAN_LITERAL.check(condition)) {
            return changed;
        }

        JmmNode replacement = condition.get("value").equals("true")
                ? deepCopy(node.getChild(1))
                : makeEmptyCompound(node);

        copyLocation(node, replacement);
        node.replace(replacement);
        return true;
    }

    private Boolean visitWhileStmt(JmmNode node, Void unused) {
        boolean changed = defaultVisit(node, unused);

        var condition = node.getChild(0);
        if (BOOLEAN_LITERAL.check(condition) && condition.get("value").equals("false")) {
            node.replace(makeEmptyCompound(node));
            return true;
        }

        return changed;
    }

    private Boolean visitDoWhileStmt(JmmNode node, Void unused) {
        boolean changed = defaultVisit(node, unused);

        var condition = node.getChild(node.getNumChildren() - 1);
        if (BOOLEAN_LITERAL.check(condition) && condition.get("value").equals("false")) {
            var body = deepCopy(node.getChild(0));
            copyLocation(node, body);
            node.replace(body);
            return true;
        }

        return changed;
    }

    private JmmNode makeEmptyCompound(JmmNode referenceNode) {
        var compound = new JmmNodeImpl(COMPOUND_STMT);
        copyLocation(referenceNode, compound);
        return compound;
    }

    private JmmNode deepCopy(JmmNode node) {
        return NodeUtils.cloneSubtree(node);
    }

    private void copyLocation(JmmNode source, JmmNode target) {
        for (var attr : List.of("lineStart", "colStart", "lineEnd", "colEnd")) {
            source.getOptionalObject(attr).ifPresent(v -> target.putObject(attr, v));
        }
    }
}