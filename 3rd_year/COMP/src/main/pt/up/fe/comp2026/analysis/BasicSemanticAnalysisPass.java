package pt.up.fe.comp2026.analysis;

import pt.up.fe.comp.jmm.analysis.table.MethodSymbol;
import pt.up.fe.comp.jmm.analysis.table.Signature;
import pt.up.fe.comp.jmm.analysis.table.Symbol;
import pt.up.fe.comp.jmm.analysis.table.SymbolTable;
import pt.up.fe.comp.jmm.analysis.table.type.JmmType;
import pt.up.fe.comp.jmm.analysis.table.type.impls.JmmArrayType;
import pt.up.fe.comp.jmm.analysis.table.type.impls.JmmClassType;
import pt.up.fe.comp.jmm.analysis.table.type.impls.JmmPrimitiveType;
import pt.up.fe.comp.jmm.ast.JmmNode;

import java.lang.reflect.Constructor;
import java.util.ArrayList;
import java.util.List;
import java.util.Optional;

import static pt.up.fe.comp2026.jmm.ast.JmmKind.*;

public class BasicSemanticAnalysisPass extends AnalysisVisitorWithTable {

    public BasicSemanticAnalysisPass(SymbolTable table) {
        super(table);
    }

    @Override
    protected void buildVisitor() {
        addVisit(METHOD_DECL, this::visitMethodDecl);
        addVisit(VAR_DECL, this::visitVarDecl);
        addVisit(VAR_REF_EXPR, this::visitVarRefExpr);
        addVisit(NEW_OBJECT_EXPR, this::visitNewObjectExpr);
        addVisit(NEW_ARRAY_EXPR, this::visitNewArrayExpr);
        addVisit(ARRAY_INIT_EXPR, this::visitArrayInitExpr);
        addVisit(ARRAY_ACCESS_EXPR, this::visitTypedExpr);
        addVisit(LENGTH_EXPR, this::visitTypedExpr);
        addVisit(UNARY_EXPR, this::visitUnaryExpr);
        addVisit(BINARY_EXPR, this::visitBinaryExpr);
        addVisit(ASSIGN_STMT, this::visitAssignStmt);
        addVisit(ARRAY_ASSIGN_STMT, this::visitArrayAssignStmt);
        addVisit(METHOD_CALL_EXPR, this::visitMethodCallExpr);
        addVisit(IMPLICIT_THIS_CALL_EXPR, this::visitTypedExpr);
        addVisit(IF_ELSE_STMT, this::visitIfElseStmt);
        addVisit(IF_STMT, this::visitIfStmt);
        addVisit(WHILE_STMT, this::visitWhileStmt);
        addVisit(DO_WHILE_STMT, this::visitDoWhileStmt);
        addVisit(FOR_STMT, this::visitForStmt);
        addVisit(RETURN_STMT, this::visitReturnStmt);
    }

    private Void visitMethodDecl(JmmNode methodDecl, SymbolTable unused) {
        var signature = types.getMethodDeclSignature(methodDecl);
        var method = table.getMethod(signature);
        if (method.isEmpty()) {
            return null;
        }

        var methodSymbol = method.get();
        var hasReturn = !methodDecl.getDescendants(RETURN_STMT).isEmpty();
        var isMain = methodSymbol.isStatic() && methodSymbol.name().equals("main");

        if (!methodSymbol.returnType().equals(JmmPrimitiveType.VOID) && !hasReturn) {
            addReport(newError(methodDecl, "Method '" + methodSymbol.name() + "' must have a return statement"));
        }

        if (isMain && hasReturn) {
            addReport(newError(methodDecl, "Main method must not contain return statements"));
        }

        return null;
    }

    private Void visitVarDecl(JmmNode varDecl, SymbolTable unused) {
        var initNodeOpt = varDecl.getOptionalObject("initNode").map(node -> (JmmNode) node);
        if (initNodeOpt.isEmpty()) {
            return null;
        }

        var initNode = initNodeOpt.get();
        if (initNode.getNumChildren() == 0) {
            return null;
        }

        var declaredType = types.convertType(varDecl.getObject("typeNode", JmmNode.class));
        var valueType = getExprType(initNode.getChild(0));
        if (valueType.isEmpty()) {
            return null;
        }

        if (!isAssignable(declaredType, valueType.get())) {
            addReport(newError(varDecl, "Cannot initialize variable '" + varDecl.get("name")
                    + "' of type '" + declaredType.print()
                    + "' with expression of type '" + valueType.get().print() + "'"));
        }

        return null;
    }

    private Void visitVarRefExpr(JmmNode varRefExpr, SymbolTable unused) {
        try {
            types.getExprType(varRefExpr);
        } catch (RuntimeException e) {
            addReport(newError(varRefExpr, e.getMessage()));
        }

        return null;
    }

    private Void visitNewObjectExpr(JmmNode newObjectExpr, SymbolTable unused) {
        var className = newObjectExpr.get("name");
        var isKnownClass = className.equals(table.getClassName())
                || table.getImportedFullyQualifiedName(className).isPresent()
                || this.table.isImplicitImport(className);

        if (!isKnownClass) {
            addReport(newError(newObjectExpr, "Class '" + className + "' is not available for instantiation"));
            return null;
        }

        var argTypes = getArgumentTypes(newObjectExpr);
        if (argTypes.isEmpty()) {
            return null;
        }

        if (className.equals(table.getClassName())) {
            if (!argTypes.get().isEmpty()) {
                addReport(newError(newObjectExpr, "Class '" + className + "' does not define a matching constructor"));
            }
            return null;
        }

        var fullyQualifiedName = table.getImportedFullyQualifiedName(className)
                .or(() -> table.getImplicitImport(className).map(SymbolTable::getFullyQualifiedName))
                .orElse(className);

        if (!hasCompatibleConstructor(fullyQualifiedName, argTypes.get())) {
            addReport(newError(newObjectExpr, "Class '" + className + "' does not define a matching constructor"));
        }

        return null;
    }

    private Void visitNewArrayExpr(JmmNode newArrayExpr, SymbolTable unused) {
        for (var sizeExpr : newArrayExpr.getChildren()) {
            var sizeType = getExprType(sizeExpr);
            if (sizeType.isEmpty()) {
                return null;
            }

            if (!sizeType.get().equals(JmmPrimitiveType.INT)) {
                addReport(newError(newArrayExpr, "Array size must be an integer"));
            }
        }

        return null;
    }

    private Void visitArrayInitExpr(JmmNode arrayInitExpr, SymbolTable unused) {
        for (var arg : types.getArgumentNodes(arrayInitExpr)) {
            var argType = getExprType(arg);
            if (argType.isEmpty()) {
                return null;
            }

            if (!argType.get().equals(JmmPrimitiveType.INT)) {
                addReport(newError(arrayInitExpr, "Array initializer elements must be integers"));
            }
        }

        return null;
    }

    private Void visitTypedExpr(JmmNode expr, SymbolTable unused) {
        getExprType(expr);
        return null;
    }

    private Void visitUnaryExpr(JmmNode unaryExpr, SymbolTable unused) {
        var parent = unaryExpr.getParent();
        if (parent != null
                && parent.getNumChildren() > 0
                && parent.getChild(0) == unaryExpr
                && (METHOD_CALL_EXPR.check(parent)
                || FIELD_ACCESS_EXPR.check(parent)
                || LENGTH_EXPR.check(parent)
                || ARRAY_ACCESS_EXPR.check(parent))) {
            return null;
        }

        var operandType = getExprType(unaryExpr.getChild(0));
        if (operandType.isEmpty()) {
            return null;
        }

        var operator = unaryExpr.get("op");
        switch (operator) {
            case "!" -> {
                if (!operandType.get().equals(JmmPrimitiveType.BOOLEAN)) {
                    addReport(newError(unaryExpr, "Operator '!' expects a boolean operand"));
                }
            }
            case "+", "-", "++", "--" -> {
                if (!operandType.get().equals(JmmPrimitiveType.INT)) {
                    addReport(newError(unaryExpr, "Operator '" + operator + "' expects an integer operand"));
                }
            }
            default -> addReport(newError(unaryExpr, "Unknown unary operator '" + operator + "'"));
        }

        return null;
    }

    private Void visitBinaryExpr(JmmNode binaryExpr, SymbolTable unused) {
        var leftType = getExprType(binaryExpr.getChild(0));
        var rightType = getExprType(binaryExpr.getChild(1));
        if (leftType.isEmpty() || rightType.isEmpty()) {
            return null;
        }

        var operator = binaryExpr.get("op");
        var left = leftType.get();
        var right = rightType.get();

        switch (operator) {
            case "+", "-", "*", "/", "%" -> {
                if (!left.equals(JmmPrimitiveType.INT) || !right.equals(JmmPrimitiveType.INT)) {
                    addReport(newError(binaryExpr, "Arithmetic operators require integer operands"));
                }
            }
            case "<", ">", "<=", ">=" -> {
                if (!left.equals(JmmPrimitiveType.INT) || !right.equals(JmmPrimitiveType.INT)) {
                    addReport(newError(binaryExpr, "Comparison operators require integer operands"));
                }
            }
            case "&&", "||" -> {
                if (!left.equals(JmmPrimitiveType.BOOLEAN) || !right.equals(JmmPrimitiveType.BOOLEAN)) {
                    addReport(newError(binaryExpr, "Logical operators require boolean operands"));
                }
            }
            case "==", "!=" -> {
                if (!isAssignable(left, right) && !isAssignable(right, left)) {
                    addReport(newError(binaryExpr, "Equality operators require compatible operand types"));
                }
            }
            default -> {
            }
        }

        return null;
    }

    private Void visitAssignStmt(JmmNode assignStmt, SymbolTable unused) {
        var targetType = resolveVariableType(assignStmt.get("var"), assignStmt);
        var valueType = getExprType(assignStmt.getChild(0));

        if (targetType.isEmpty() || valueType.isEmpty()) {
            return null;
        }

        if (!isAssignable(targetType.get(), valueType.get())) {
            addReport(newError(assignStmt, "Cannot assign expression of type '" + valueType.get().print()
                    + "' to variable of type '" + targetType.get().print() + "'"));
        }

        return null;
    }

    private Void visitArrayAssignStmt(JmmNode arrayAssignStmt, SymbolTable unused) {
        var arrayType = resolveVariableType(arrayAssignStmt.get("array"), arrayAssignStmt);
        var children = arrayAssignStmt.getChildren();
        if (children.isEmpty()) {
            return null;
        }

        var indexExprs = children.subList(0, children.size() - 1);
        var valueExpr = children.get(children.size() - 1);
        var valueType = getExprType(valueExpr);

        if (arrayType.isEmpty() || valueType.isEmpty()) {
            return null;
        }

        JmmType targetType = arrayType.get();
        if (!targetType.isArray()) {
            addReport(newError(arrayAssignStmt, "Target '" + arrayAssignStmt.get("array") + "' is not an array"));
            return null;
        }

        for (var indexExpr : indexExprs) {
            var indexType = getExprType(indexExpr);
            if (indexType.isEmpty()) {
                return null;
            }

            if (!indexType.get().equals(JmmPrimitiveType.INT)) {
                addReport(newError(arrayAssignStmt, "Array index must be an integer"));
            }

            if (!targetType.isArray()) {
                addReport(newError(arrayAssignStmt, "Too many indices for array '" + arrayAssignStmt.get("array") + "'"));
                return null;
            }

            var arrayTarget = targetType.asArray();
            targetType = arrayTarget.dimension() == 1
                    ? arrayTarget.itemType()
                    : new JmmArrayType(arrayTarget.itemType(), arrayTarget.dimension() - 1);
        }

        if (!isAssignable(targetType, valueType.get())) {
            addReport(newError(arrayAssignStmt, "Cannot assign value of type '" + valueType.get().print()
                    + "' to array item of type '" + targetType.print() + "'"));
        }

        return null;
    }

    private Void visitMethodCallExpr(JmmNode methodCallExpr, SymbolTable unused) {
        return visitTypedExpr(methodCallExpr, unused);
    }

    private Void visitIfElseStmt(JmmNode ifElseStmt, SymbolTable unused) {
        checkBooleanCondition(ifElseStmt.getChild(0), ifElseStmt, "If condition must be boolean");
        return null;
    }

    private Void visitIfStmt(JmmNode ifStmt, SymbolTable unused) {
        checkBooleanCondition(ifStmt.getChild(0), ifStmt, "If condition must be boolean");
        return null;
    }

    private Void visitWhileStmt(JmmNode whileStmt, SymbolTable unused) {
        checkBooleanCondition(whileStmt.getChild(0), whileStmt, "While condition must be boolean");
        return null;
    }

    private Void visitDoWhileStmt(JmmNode doWhileStmt, SymbolTable unused) {
        checkBooleanCondition(doWhileStmt.getChild(doWhileStmt.getNumChildren() - 1), doWhileStmt,
                "Do-while condition must be boolean");
        return null;
    }

    private Void visitForStmt(JmmNode forStmt, SymbolTable unused) {
        var initNode = forStmt.getObject("initNode", JmmNode.class);
        checkForAssignment(initNode, forStmt);

        var conditionNode = forStmt.getObject("conditionNode", JmmNode.class);
        if (conditionNode.getNumChildren() > 0) {
            checkBooleanCondition(conditionNode.getChild(0), forStmt, "For condition must be boolean");
        }

        var updateNode = forStmt.getObject("updateNode", JmmNode.class);
        checkForAssignment(updateNode, forStmt);

        return null;
    }

    private Void visitReturnStmt(JmmNode returnStmt, SymbolTable unused) {
        var methodDecl = returnStmt.getAncestor(METHOD_DECL);
        if (methodDecl.isEmpty()) {
            return null;
        }

        var signature = types.getMethodDeclSignature(methodDecl.get());
        var method = table.getMethod(signature);
        if (method.isEmpty()) {
            return null;
        }

        var returnType = method.get().returnType();

        if (returnStmt.getNumChildren() == 0) {
            if (!returnType.equals(JmmPrimitiveType.VOID)) {
                addReport(newError(returnStmt, "Non-void method must return a value"));
            }
            return null;
        }

        if (returnType.equals(JmmPrimitiveType.VOID)) {
            addReport(newError(returnStmt, "Void method must not return a value"));
            return null;
        }

        var exprType = getExprType(returnStmt.getChild(0));
        if (exprType.isEmpty()) {
            return null;
        }

        if (!isAssignable(returnType, exprType.get())) {
            addReport(newError(returnStmt, "Return type '" + exprType.get().print()
                    + "' is not compatible with method return type '" + returnType.print() + "'"));
        }

        return null;
    }

    private void checkBooleanCondition(JmmNode expr, JmmNode stmt, String message) {
        var exprType = getExprType(expr);
        if (exprType.isEmpty()) {
            return;
        }

        if (!exprType.get().equals(JmmPrimitiveType.BOOLEAN)) {
            addReport(newError(stmt, message));
        }
    }

    private Optional<JmmType> getExprType(JmmNode expr) {
        try {
            return Optional.of(types.getExprType(expr));
        } catch (RuntimeException e) {
            addReport(newError(expr, e.getMessage()));
            return Optional.empty();
        }
    }

    private Optional<JmmType> resolveVariableType(String name, JmmNode context) {
        var methodDecl = context.getAncestor(METHOD_DECL);
        if (methodDecl.isPresent()) {
            var signature = types.getMethodDeclSignature(methodDecl.get());
            var method = table.getMethod(signature);

            if (method.isPresent()) {
                var local = method.get().getLocalVariable(name);
                if (local.isPresent()) {
                    return Optional.of(local.get().type());
                }

                var param = method.get().getParameter(name);
                if (param.isPresent()) {
                    return Optional.of(param.get().type());
                }
            }
        }

        var field = table.getField(name);
        if (field.isPresent()) {
            return Optional.of(field.get().type());
        }

        addReport(newError(context, "Variable '" + name + "' is not defined"));
        return Optional.empty();
    }

    private void checkForAssignment(JmmNode clauseNode, JmmNode context) {
        if (clauseNode.getNumChildren() == 0) {
            return;
        }

        var forAssign = clauseNode.getChild(0);
        var targetType = resolveVariableType(forAssign.get("name"), context);
        var valueType = getExprType(forAssign.getChild(0));
        if (targetType.isEmpty() || valueType.isEmpty()) {
            return;
        }

        if (!isAssignable(targetType.get(), valueType.get())) {
            addReport(newError(forAssign, "Cannot assign expression of type '" + valueType.get().print()
                    + "' to variable of type '" + targetType.get().print() + "'"));
        }
    }

    private Optional<List<JmmType>> getArgumentTypes(JmmNode nodeWithArgs) {
        var argTypes = new ArrayList<JmmType>();
        for (var arg : types.getArgumentNodes(nodeWithArgs)) {
            var argType = getExprType(arg);
            if (argType.isEmpty()) {
                return Optional.empty();
            }
            argTypes.add(argType.get());
        }

        return Optional.of(argTypes);
    }

    private boolean hasCompatibleConstructor(String className, List<JmmType> argTypes) {
        var importedTable = table.getImportedSymbolTable(className);
        if (importedTable.isPresent()) {
            for (var constructor : importedTable.get().getMethods("<init>")) {
                if (constructor.parameters().size() != argTypes.size()) {
                    continue;
                }

                var matches = true;
                for (int i = 0; i < argTypes.size(); i++) {
                    if (!isAssignable(constructor.parameters().get(i).type(), argTypes.get(i))) {
                        matches = false;
                        break;
                    }
                }

                if (matches) {
                    return true;
                }
            }
        }

        return hasCompatibleExternalConstructor(className, argTypes);
    }

    private boolean hasCompatibleExternalConstructor(String className, List<JmmType> argTypes) {
        try {
            var externalClass = Class.forName(className);
            for (Constructor<?> constructor : externalClass.getDeclaredConstructors()) {
                if (constructor.getParameterCount() != argTypes.size()) {
                    continue;
                }

                var matches = true;
                var parameterTypes = constructor.getParameterTypes();
                for (int i = 0; i < parameterTypes.length; i++) {
                    if (!isAssignable(convertReflectionType(parameterTypes[i]), argTypes.get(i))) {
                        matches = false;
                        break;
                    }
                }

                if (matches) {
                    return true;
                }
            }
        } catch (ClassNotFoundException ignored) {
            return false;
        }

        return false;
    }

    private JmmType convertReflectionType(Class<?> type) {
        int dimensions = 0;
        while (type.isArray()) {
            dimensions++;
            type = type.getComponentType();
        }

        JmmType baseType = type.isPrimitive()
                ? JmmPrimitiveType.fromString(type.getName()).orElseThrow()
                : JmmClassType.ofInstance(type.getName(), true);

        if (dimensions > 0) {
            return new JmmArrayType(baseType, dimensions);
        }

        return baseType;
    }

    private boolean isAssignable(JmmType targetType, JmmType valueType) {
        if (targetType.equals(valueType)) {
            return true;
        }

        if (targetType.isArray() || valueType.isArray()) {
            return targetType.equals(valueType);
        }

        if (targetType.isPrimitive() || valueType.isPrimitive()) {
            return false;
        }

        var targetClass = targetType.asClass();
        var valueClass = valueType.asClass();

        if (targetClass.staticRef() != valueClass.staticRef()) {
            return false;
        }

        return isSubtype(valueClass.fullyQualifiedName(), targetClass.fullyQualifiedName());
    }

    private boolean isSubtype(String childType, String parentType) {
        if (childType.equals(parentType)) {
            return true;
        }

        // Every class implicitly extends java.lang.Object
        if (parentType.equals("java.lang.Object") || parentType.equals("Object")) {
            return true;
        }

        String current = getDirectSuper(childType);
        while (current != null) {
            if (current.equals(parentType)) {
                return true;
            }

            current = getDirectSuper(current);
        }

        return false;
    }

    private String getDirectSuper(String className) {
        if (className.equals(table.getFullyQualifiedName())) {
            return table.getSuperFullyQualifiedName();
        }

        var importedTable = table.getImportedSymbolTable(className);
        if (importedTable.isPresent()) {
            return importedTable.get().getSuperFullyQualifiedName();
        }

        return null;
    }
}
