package pt.up.fe.comp2026.optimization;

import pt.up.fe.comp.jmm.ast.AJmmVisitor;
import pt.up.fe.comp.jmm.ast.JmmNode;
import pt.up.fe.comp.jmm.ast.JmmNodeImpl;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

import static pt.up.fe.comp2026.jmm.ast.JmmKind.*;

/**
 * AST visitor that performs constant propagation within methods.
 * Replaces VarRefExpr nodes with known constant literal values.
 */
public class ConstantPropagationVisitor extends AJmmVisitor<Map<String, JmmNode>, Boolean> {

    @Override
    protected void buildVisitor() {
        addVisit(METHOD_DECL, this::visitMethodDecl);
        setDefaultVisit(this::defaultVisit);
    }

    public boolean propagate(JmmNode root) {
        return visit(root, new HashMap<>()) == Boolean.TRUE;
    }

    private Boolean defaultVisit(JmmNode node, Map<String, JmmNode> constants) {
        boolean changed = false;
        for (int i = 0; i < node.getNumChildren(); i++) {
            if (visitChild(node, i, constants) == Boolean.TRUE) {
                changed = true;
            }
        }
        return changed;
    }

    private Boolean visitChild(JmmNode parent, int index, Map<String, JmmNode> constants) {
        var child = parent.getChild(index);
        return visit(child, constants);
    }

    private Boolean visitMethodDecl(JmmNode node, Map<String, JmmNode> outerConstants) {
        var constants = new HashMap<String, JmmNode>();
        boolean changed = false;
        for (var stmt : node.getChildren(STMT)) {
            if (visitStmt(stmt, constants) == Boolean.TRUE) changed = true;
        }
        return changed;
    }

    private Boolean visitStmt(JmmNode node, Map<String, JmmNode> constants) {
        if (ASSIGN_STMT.check(node)) return visitAssignStmt(node, constants);
        if (COMPOUND_STMT.check(node)) return visitCompoundStmt(node, constants);
        if (IF_ELSE_STMT.check(node)) return visitIfElseStmt(node, constants);
        if (IF_STMT.check(node)) return visitIfStmt(node, constants);
        if (WHILE_STMT.check(node) || DO_WHILE_STMT.check(node) || FOR_STMT.check(node))
            return visitLoop(node, constants);
        if (RETURN_STMT.check(node)) return visitReturn(node, constants);
        if (EXPR_STMT.check(node)) return visitExprStmt(node, constants);
        // For unknown statement types, just propagate into children
        return propagateIntoChildren(node, constants);
    }

    private Boolean visitCompoundStmt(JmmNode node, Map<String, JmmNode> constants) {
        boolean changed = false;
        for (var stmt : node.getChildren(STMT)) {
            if (visitStmt(stmt, constants) == Boolean.TRUE) changed = true;
        }
        return changed;
    }

    private Boolean visitAssignStmt(JmmNode node, Map<String, JmmNode> constants) {
        boolean changed = false;
        // Propagate into RHS first
        if (node.getNumChildren() > 0) {
            if (propagateExpr(node.getChild(0), constants) == Boolean.TRUE) changed = true;
        }

        var varName = node.get("var");
        var rhs = node.getNumChildren() > 0 ? node.getChild(0) : null;

        if (rhs != null && (INTEGER_LITERAL.check(rhs) || BOOLEAN_LITERAL.check(rhs))) {
            constants.put(varName, rhs);
        } else {
            constants.remove(varName);
        }

        return changed;
    }

    private Boolean visitReturn(JmmNode node, Map<String, JmmNode> constants) {
        return propagateIntoChildren(node, constants);
    }

    private Boolean visitExprStmt(JmmNode node, Map<String, JmmNode> constants) {
        return propagateIntoChildren(node, constants);
    }

    private Boolean visitIfElseStmt(JmmNode node, Map<String, JmmNode> constants) {
        boolean changed = false;
        // Condition
        if (node.getNumChildren() > 0 && propagateExpr(node.getChild(0), constants) == Boolean.TRUE) changed = true;

        var thenConstants = new HashMap<>(constants);
        var elseConstants = new HashMap<>(constants);

        if (node.getNumChildren() > 1 && visitStmt(node.getChild(1), thenConstants) == Boolean.TRUE) changed = true;
        if (node.getNumChildren() > 2 && visitStmt(node.getChild(2), elseConstants) == Boolean.TRUE) changed = true;

        // Merge: keep only constants consistent in both branches
        mergeConstants(constants, thenConstants, elseConstants);
        return changed;
    }

    private Boolean visitIfStmt(JmmNode node, Map<String, JmmNode> constants) {
        boolean changed = false;
        if (node.getNumChildren() > 0 && propagateExpr(node.getChild(0), constants) == Boolean.TRUE) changed = true;

        var thenConstants = new HashMap<>(constants);
        if (node.getNumChildren() > 1 && visitStmt(node.getChild(1), thenConstants) == Boolean.TRUE) changed = true;

        // After if-only: keep constants that weren't modified in the then branch
        retainConsistentConstants(constants, thenConstants);
        return changed;
    }

    private Boolean visitLoop(JmmNode node, Map<String, JmmNode> constants) {
        // Conservative: any variable that might be modified in the loop loses its constant value
        boolean changed = propagateIntoChildren(node, constants);

        // Invalidate any variable that appears as LHS of an assignment inside the loop
        var assigned = collectAssignedVars(node);
        for (var v : assigned) {
            constants.remove(v);
        }
        return changed;
    }

    private Boolean propagateIntoChildren(JmmNode node, Map<String, JmmNode> constants) {
        boolean changed = false;
        for (int i = 0; i < node.getNumChildren(); i++) {
            if (propagateExpr(node.getChild(i), constants) == Boolean.TRUE) changed = true;
        }
        return changed;
    }

    /**
     * Recursively propagates constants into an expression node.
     * Returns true if any substitution was made.
     */
    private Boolean propagateExpr(JmmNode node, Map<String, JmmNode> constants) {
        if (VAR_REF_EXPR.check(node)) {
            return visitVarRef(node, constants);
        }

        boolean changed = false;
        for (int i = 0; i < node.getNumChildren(); i++) {
            if (propagateExpr(node.getChild(i), constants) == Boolean.TRUE) {
                changed = true;
            }
        }
        return changed;
    }

    private Boolean visitVarRef(JmmNode node, Map<String, JmmNode> constants) {
        var name = node.get("name");
        if (constants.containsKey(name)) {
            var constNode = constants.get(name);
            var replacement = new JmmNodeImpl(constNode.getKind());
            replacement.put("value", constNode.get("value"));
            copyLocation(node, replacement);
            node.replace(replacement);
            return true;
        }
        return false;
    }

    private void mergeConstants(Map<String, JmmNode> result,
                                 Map<String, JmmNode> thenMap,
                                 Map<String, JmmNode> elseMap) {
        var toRemove = new ArrayList<String>();
        for (var key : result.keySet()) {
            var thenVal = thenMap.get(key);
            var elseVal = elseMap.get(key);
            if (thenVal == null || elseVal == null
                    || !thenVal.get("value").equals(elseVal.get("value"))) {
                toRemove.add(key);
            }
        }
        for (var key : toRemove) result.remove(key);
    }

    private void retainConsistentConstants(Map<String, JmmNode> result,
                                            Map<String, JmmNode> afterBranch) {
        var toRemove = new ArrayList<String>();
        for (var key : result.keySet()) {
            var afterVal = afterBranch.get(key);
            if (afterVal == null || !afterVal.get("value").equals(result.get(key).get("value"))) {
                toRemove.add(key);
            }
        }
        for (var key : toRemove) result.remove(key);
    }

    private List<String> collectAssignedVars(JmmNode node) {
        var vars = new ArrayList<String>();
        if (ASSIGN_STMT.check(node)) {
            vars.add(node.get("var"));
        }
        for (var child : node.getChildren()) {
            vars.addAll(collectAssignedVars(child));
        }
        return vars;
    }

    private void copyLocation(JmmNode source, JmmNode target) {
        for (var attr : List.of("lineStart", "colStart", "lineEnd", "colEnd")) {
            source.getOptionalObject(attr).ifPresent(v -> target.putObject(attr, v));
        }
    }
}
