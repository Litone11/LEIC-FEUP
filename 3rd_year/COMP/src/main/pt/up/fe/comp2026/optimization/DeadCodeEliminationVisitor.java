package pt.up.fe.comp2026.optimization;

import pt.up.fe.comp.jmm.analysis.table.MethodSymbol;
import pt.up.fe.comp.jmm.analysis.table.SymbolTable;
import pt.up.fe.comp.jmm.ast.AJmmVisitor;
import pt.up.fe.comp.jmm.ast.JmmNode;
import pt.up.fe.comp.jmm.ast.JmmNodeImpl;
import pt.up.fe.comp2026.ast.NodeUtils;
import pt.up.fe.comp2026.ast.TypeUtils;

import java.util.ArrayList;
import java.util.HashSet;
import java.util.List;
import java.util.Set;

import static pt.up.fe.comp2026.jmm.ast.JmmKind.*;

/**
 * Eliminates dead assignments to locals and dead pure expression statements.
 */
public class DeadCodeEliminationVisitor extends AJmmVisitor<Void, Boolean> {

    private final SymbolTable table;
    private final TypeUtils types;
    private MethodSymbol currentMethod;

    private record Analysis(Set<String> liveBefore, boolean changed) {
    }

    public DeadCodeEliminationVisitor(SymbolTable table) {
        this.table = table;
        this.types = new TypeUtils(table);
    }

    @Override
    protected void buildVisitor() {
        addVisit(METHOD_DECL, this::visitMethodDecl);
        setDefaultVisit(this::defaultVisit);
    }

    public boolean eliminate(JmmNode root) {
        return visit(root) == Boolean.TRUE;
    }

    private Boolean defaultVisit(JmmNode node, Void unused) {
        boolean changed = false;
        for (var child : node.getChildren()) {
            if (visit(child) == Boolean.TRUE) {
                changed = true;
            }
        }
        return changed;
    }

    private Boolean visitMethodDecl(JmmNode node, Void unused) {
        currentMethod = table.getMethod(types.getMethodDeclSignature(node)).orElseThrow();
        var result = analyzeStmtList(new ArrayList<>(node.getChildren(STMT)), new HashSet<>());
        currentMethod = null;
        return result.changed();
    }

    private Analysis analyzeStmtList(List<JmmNode> statements, Set<String> liveAfter) {
        Set<String> live = new HashSet<>(liveAfter);
        boolean changed = false;

        for (int i = statements.size() - 1; i >= 0; i--) {
            var analysis = analyzeStmt(statements.get(i), live);
            live = analysis.liveBefore();
            changed |= analysis.changed();
        }

        return new Analysis(live, changed);
    }

    private Analysis analyzeStmt(JmmNode stmt, Set<String> liveAfter) {
        if (COMPOUND_STMT.check(stmt)) {
            return analyzeStmtList(new ArrayList<>(stmt.getChildren(STMT)), liveAfter);
        }

        if (ASSIGN_STMT.check(stmt)) {
            return analyzeAssignStmt(stmt, liveAfter);
        }

        if (ARRAY_ASSIGN_STMT.check(stmt)) {
            return analyzeArrayAssignStmt(stmt, liveAfter);
        }

        if (EXPR_STMT.check(stmt)) {
            return analyzeExprStmt(stmt, liveAfter);
        }

        if (RETURN_STMT.check(stmt)) {
            return new Analysis(collectUsedVariablesInChildren(stmt), false);
        }

        if (IF_ELSE_STMT.check(stmt)) {
            return analyzeIfElseStmt(stmt, liveAfter);
        }

        if (IF_STMT.check(stmt)) {
            return analyzeIfStmt(stmt, liveAfter);
        }

        if (WHILE_STMT.check(stmt) || DO_WHILE_STMT.check(stmt) || FOR_STMT.check(stmt)) {
            var liveBefore = new HashSet<>(liveAfter);
            liveBefore.addAll(collectUsedVariablesInChildren(stmt));
            return new Analysis(liveBefore, false);
        }

        var liveBefore = new HashSet<>(liveAfter);
        liveBefore.addAll(collectUsedVariablesInChildren(stmt));
        return new Analysis(liveBefore, false);
    }

    private Analysis analyzeAssignStmt(JmmNode stmt, Set<String> liveAfter) {
        var target = stmt.get("var");
        var rhs = stmt.getChild(0);
        var rhsUses = collectUsedVariables(rhs);
        boolean hasSideEffects = hasSideEffects(rhs);
        boolean removableTarget = isLocalOrParam(target);

        if (removableTarget && !liveAfter.contains(target)) {
            if (hasSideEffects) {
                replaceWithExprStmt(stmt, rhs);
                var liveBefore = new HashSet<>(liveAfter);
                liveBefore.addAll(rhsUses);
                return new Analysis(liveBefore, true);
            }

            stmt.delete();
            return new Analysis(new HashSet<>(liveAfter), true);
        }

        var liveBefore = new HashSet<>(liveAfter);
        if (removableTarget) {
            liveBefore.remove(target);
        }
        liveBefore.addAll(rhsUses);
        return new Analysis(liveBefore, false);
    }

    private Analysis analyzeArrayAssignStmt(JmmNode stmt, Set<String> liveAfter) {
        var arrayName = stmt.get("array");
        var children = stmt.getChildren();
        if (children.isEmpty()) {
            return new Analysis(new HashSet<>(liveAfter), false);
        }

        var valueNode = children.get(children.size() - 1);
        var indexNodes = children.subList(0, children.size() - 1);

        var usedVars = new HashSet<String>();
        boolean exprSideEffects = hasSideEffects(valueNode);
        usedVars.addAll(collectUsedVariables(valueNode));

        for (var indexNode : indexNodes) {
            exprSideEffects |= hasSideEffects(indexNode);
            usedVars.addAll(collectUsedVariables(indexNode));
        }

        boolean localArray = isLocalVariable(arrayName);
        if (localArray && !liveAfter.contains(arrayName) && !exprSideEffects) {
            stmt.delete();
            return new Analysis(new HashSet<>(liveAfter), true);
        }

        var liveBefore = new HashSet<>(liveAfter);
        liveBefore.addAll(usedVars);
        if (isLocalOrParam(arrayName)) {
            liveBefore.add(arrayName);
        }

        return new Analysis(liveBefore, false);
    }

    private Analysis analyzeExprStmt(JmmNode stmt, Set<String> liveAfter) {
        if (stmt.getNumChildren() == 0) {
            stmt.delete();
            return new Analysis(new HashSet<>(liveAfter), true);
        }

        var expr = stmt.getChild(0);
        if (!hasSideEffects(expr)) {
            stmt.delete();
            return new Analysis(new HashSet<>(liveAfter), true);
        }

        var liveBefore = new HashSet<>(liveAfter);
        liveBefore.addAll(collectUsedVariables(expr));
        return new Analysis(liveBefore, false);
    }

    private Analysis analyzeIfElseStmt(JmmNode stmt, Set<String> liveAfter) {
        var thenAnalysis = analyzeStmt(stmt.getChild(1), new HashSet<>(liveAfter));
        var elseAnalysis = analyzeStmt(stmt.getChild(2), new HashSet<>(liveAfter));

        var liveBefore = new HashSet<>(thenAnalysis.liveBefore());
        liveBefore.addAll(elseAnalysis.liveBefore());
        liveBefore.addAll(collectUsedVariables(stmt.getChild(0)));

        return new Analysis(liveBefore, thenAnalysis.changed() || elseAnalysis.changed());
    }

    private Analysis analyzeIfStmt(JmmNode stmt, Set<String> liveAfter) {
        var thenAnalysis = analyzeStmt(stmt.getChild(1), new HashSet<>(liveAfter));

        var liveBefore = new HashSet<>(liveAfter);
        liveBefore.addAll(thenAnalysis.liveBefore());
        liveBefore.addAll(collectUsedVariables(stmt.getChild(0)));

        return new Analysis(liveBefore, thenAnalysis.changed());
    }

    private Set<String> collectUsedVariablesInChildren(JmmNode node) {
        var used = new HashSet<String>();
        for (var child : node.getChildren()) {
            used.addAll(collectUsedVariables(child));
        }
        return used;
    }

    private Set<String> collectUsedVariables(JmmNode node) {
        var used = new HashSet<String>();
        collectUsedVariables(node, used);
        return used;
    }

    private void collectUsedVariables(JmmNode node, Set<String> used) {
        if (VAR_REF_EXPR.check(node)) {
            var name = node.get("name");
            if (isLocalOrParam(name)) {
                used.add(name);
            }
            return;
        }

        for (var child : node.getChildren()) {
            collectUsedVariables(child, used);
        }
    }

    private boolean hasSideEffects(JmmNode node) {
        if (METHOD_CALL_EXPR.check(node) || IMPLICIT_THIS_CALL_EXPR.check(node)) {
            return true;
        }

        for (var child : node.getChildren()) {
            if (hasSideEffects(child)) {
                return true;
            }
        }

        return false;
    }

    private boolean isLocalOrParam(String name) {
        return currentMethod != null
                && (currentMethod.getLocalVariable(name).isPresent() || currentMethod.getParameter(name).isPresent());
    }

    private boolean isLocalVariable(String name) {
        return currentMethod != null && currentMethod.getLocalVariable(name).isPresent();
    }

    private void replaceWithExprStmt(JmmNode targetStmt, JmmNode expr) {
        var exprStmt = new JmmNodeImpl(EXPR_STMT);
        copyLocation(targetStmt, exprStmt);
        exprStmt.add(NodeUtils.cloneSubtree(expr));
        targetStmt.replace(exprStmt);
    }

    private void copyLocation(JmmNode source, JmmNode target) {
        for (var attr : List.of("lineStart", "colStart", "lineEnd", "colEnd")) {
            source.getOptionalObject(attr).ifPresent(v -> target.putObject(attr, v));
        }
    }
}