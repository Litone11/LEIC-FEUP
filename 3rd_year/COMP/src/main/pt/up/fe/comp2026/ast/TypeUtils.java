package pt.up.fe.comp2026.ast;

import pt.up.fe.comp.jmm.analysis.table.MethodSymbol;
import pt.up.fe.comp.jmm.analysis.table.Signature;
import pt.up.fe.comp.jmm.analysis.table.Symbol;
import pt.up.fe.comp.jmm.analysis.table.SymbolTable;
import pt.up.fe.comp.jmm.analysis.table.type.impls.JmmPrimitiveType;
import pt.up.fe.comp.jmm.analysis.table.type.JmmType;
import pt.up.fe.comp.jmm.analysis.table.type.impls.JmmArrayType;
import pt.up.fe.comp.jmm.analysis.table.type.impls.JmmClassType;
import pt.up.fe.comp.jmm.ast.JmmNode;
import pt.up.fe.comp.jmm.ast.JmmNodeImpl;
import pt.up.fe.comp2026.symboltable.JmmSymbolTable;

import java.lang.reflect.Method;
import java.lang.reflect.Modifier;
import java.util.List;
import java.util.Optional;

import static pt.up.fe.comp2026.jmm.ast.JmmKind.*;


/**
 * Utility methods regarding types.
 */
public class TypeUtils {

    private record MisparsedUnaryTarget(String operator, JmmNode operand) {
    }

    private record ResolvedMethod(JmmType returnType, boolean isStatic, String ownerClassName, List<JmmType> parameterTypes) {
    }

    private final JmmSymbolTable table;

    public TypeUtils(SymbolTable table) {
        this.table = (JmmSymbolTable) table;
    }

    public static TypeUtils with(SymbolTable table) {
        return new TypeUtils(table);
    }

    public static JmmPrimitiveType intType() {
        return JmmPrimitiveType.INT;
    }

    public JmmType convertType(JmmNode typeNode) {
        assert (TYPE.check(typeNode));

        var name = typeNode.get("name");
        var arrayDims = typeNode.getInteger("arrayDims", 0);

        JmmType baseType = JmmPrimitiveType.fromString(name)
                .<JmmType>map(type -> type)
                .orElseGet(() -> resolveClassType(name));

        if (arrayDims > 0) {
            return JmmArrayType.of(baseType, arrayDims);
        }

        return baseType;
    }


    /**
     * Gets the {@link JmmType} of an arbitrary expression.
     *
     * @param expr
     * @return
     */
    public JmmType getExprType(JmmNode expr) {
        return switch (expr.getKind()) {
            case INTEGER_LITERAL -> intType();
            case BOOLEAN_LITERAL -> JmmPrimitiveType.BOOLEAN;
            case BINARY_EXPR -> getBinExprType(expr);
            case UNARY_EXPR -> getUnaryExprType(expr);
            case VAR_REF_EXPR -> getVarExprType(expr);
            case PAREN_EXPR -> getExprType(expr.getChild(0));
            case THIS_EXPR -> getThisExprType(expr);
            case NEW_OBJECT_EXPR -> resolveClassType(expr.get("name"));
            case NEW_ARRAY_EXPR -> JmmArrayType.of(intType(), expr.getInteger("arrayDims", expr.getChildren().size()));
            case ARRAY_INIT_EXPR -> JmmArrayType.of(intType());
            case ARRAY_ACCESS_EXPR -> getArrayElementType(expr);
            case LENGTH_EXPR -> getLengthExprType(expr);
            case FIELD_ACCESS_EXPR -> getFieldAccessType(expr);
            case METHOD_CALL_EXPR -> getMethodCallType(expr);
            case IMPLICIT_THIS_CALL_EXPR -> getImplicitThisCallType(expr);
            default ->
                    throw new UnsupportedOperationException("Can't compute type for expression kind '" + expr.getKind() + "'");
        };
    }


    public Signature getMethodDeclSignature(JmmNode methodDecl) {
        // Ensure given node is a MethodDecl
        METHOD_DECL.check(methodDecl);

        // Get name of the method
        var methodName = methodDecl.get("name");
        var params = methodDecl.getDescendants(PARAM).stream()
                .map(param -> convertType(param.getObject("typeNode", JmmNode.class)))
                .toList();

        // Create method signature with method name and types of parameters
        return new Signature(methodName, params);
    }


    private JmmType getBinExprType(JmmNode binaryExpr) {

        // Get operator
        String operator = binaryExpr.get("op");

        return switch (operator) {
            case "+", "-", "*", "/", "%" -> intType();
            case "<", ">", "<=", ">=", "==", "!=", "&&", "||" -> JmmPrimitiveType.BOOLEAN;
            default ->
                    throw new RuntimeException("Unknown operator '" + operator + "' of expression '" + binaryExpr + "'");
        };
    }

    private JmmType getUnaryExprType(JmmNode unaryExpr) {
        var operator = unaryExpr.get("op");

        return switch (operator) {
            case "!" -> JmmPrimitiveType.BOOLEAN;
            case "+", "-", "++", "--" -> intType();
            default -> throw new RuntimeException("Unknown unary operator '" + operator + "'");
        };
    }

    private JmmType getVarExprType(JmmNode varRefExpr) {
        var forcedStaticType = varRefExpr.getOptionalObject("forcedStaticType").map(Object::toString);
        if (forcedStaticType.isPresent()) {
            return JmmClassType.ofStaticReference(
                    forcedStaticType.get(),
                    !forcedStaticType.get().equals(table.getFullyQualifiedName()));
        }

        var name = varRefExpr.get("name");

        var methodDecl = varRefExpr.getAncestor(METHOD_DECL);
        if (methodDecl.isPresent()) {
            var signature = getMethodDeclSignature(methodDecl.get());
            var method = table.getMethod(signature);

            if (method.isPresent()) {
                var local = method.get().getLocalVariable(name);
                if (local.isPresent()) {
                    return local.get().type();
                }

                var param = method.get().getParameter(name);
                if (param.isPresent()) {
                    return param.get().type();
                }
            }
        }

        var field = table.getField(name);
        if (field.isPresent()) {
            return field.get().type();
        }

        var importedType = table.getImportedFullyQualifiedName(name);
        if (importedType.isPresent()) {
            return JmmClassType.ofStaticReference(importedType.get(), true);
        }

        var implicitType = table.getImplicitImport(name);
        if (implicitType.isPresent()) {
            return JmmClassType.ofStaticReference(implicitType.get().getFullyQualifiedName(), true);
        }

        if (name.equals(table.getClassName())) {
            return JmmClassType.ofStaticReference(table.getFullyQualifiedName(), false);
        }

        throw new RuntimeException("Could not resolve variable reference '" + name + "'");
    }

    private JmmType getArrayElementType(JmmNode arrayAccessExpr) {
        var unaryTarget = getMisparsedUnaryTarget(arrayAccessExpr);
        if (unaryTarget.isPresent()) {
            var elementType = getArrayElementType(arrayAccessExpr, getExprType(unaryTarget.get().operand()));
            return applyUnaryOperator(unaryTarget.get().operator(), elementType);
        }

        var arrayType = getExprType(arrayAccessExpr.getChild(0));
        return getArrayElementType(arrayAccessExpr, arrayType);
    }

    private JmmType getArrayElementType(JmmNode arrayAccessExpr, JmmType arrayType) {
        if (!arrayType.isArray()) {
            throw new RuntimeException("Cannot index non-array type '" + arrayType.print() + "'");
        }

        var indexType = getExprType(arrayAccessExpr.getChild(1));
        if (!indexType.equals(JmmPrimitiveType.INT)) {
            throw new RuntimeException("Array index must be an integer");
        }

        var jmmArrayType = arrayType.asArray();
        if (jmmArrayType.dimension() == 1) {
            return jmmArrayType.itemType();
        }

        return JmmArrayType.of(jmmArrayType.itemType(), jmmArrayType.dimension() - 1);
    }

    private JmmType getMethodCallType(JmmNode methodCallExpr) {
        var unaryTarget = getMisparsedUnaryTarget(methodCallExpr);
        if (unaryTarget.isPresent()) {
            var callType = getMethodCallType(methodCallExpr, getExprType(unaryTarget.get().operand()));
            return applyUnaryOperator(unaryTarget.get().operator(), callType);
        }

        var targetType = getExprType(methodCallExpr.getChild(0));
        return getMethodCallType(methodCallExpr, targetType);
    }

    private JmmType getImplicitThisCallType(JmmNode methodCallExpr) {
        var methodDecl = methodCallExpr.getAncestor(METHOD_DECL);
        var argTypes = getArgumentNodes(methodCallExpr).stream()
                .map(this::getExprType)
                .toList();

        var resolvedMethod = resolveMethod(table.getFullyQualifiedName(), methodCallExpr.get("method"), argTypes)
                .orElseThrow(() -> new RuntimeException("Could not resolve return type of method call '"
                        + methodCallExpr.get("method") + "'"));

        if (methodDecl.isPresent() && methodDecl.get().getBoolean("isStatic", false) && !resolvedMethod.isStatic()) {
            throw new RuntimeException("Cannot use implicit 'this' in a static method");
        }

        return resolvedMethod.returnType();
    }

    private JmmType getMethodCallType(JmmNode methodCallExpr, JmmType targetType) {
        if (!targetType.isClass()) {
            throw new RuntimeException("Cannot invoke method '" + methodCallExpr.get("method")
                    + "' on non-class type '" + targetType.print() + "'");
        }

        var methodName = methodCallExpr.get("method");
        var argTypes = getArgumentNodes(methodCallExpr).stream()
                .map(this::getExprType)
                .toList();
        var classType = targetType.asClass();

        var resolvedMethod = resolveMethod(classType.fullyQualifiedName(), methodName, argTypes);
        if (resolvedMethod.isEmpty()) {
            throw new RuntimeException("Could not resolve return type of method call '" + methodName + "'");
        }

        if (classType.staticRef() && !resolvedMethod.get().isStatic()) {
            throw new RuntimeException("Cannot invoke non-static method '" + methodName + "' on class '"
                    + classType.name() + "'");
        }

        if (!classType.staticRef() && resolvedMethod.get().isStatic()) {
            normalizeStaticMethodTarget(methodCallExpr, resolvedMethod.get().ownerClassName());
        }

        return resolvedMethod.get().returnType();
    }

    public List<JmmNode> getArgumentNodes(JmmNode nodeWithArgs) {
        var argsNode = nodeWithArgs.getOptionalObject("argsNode")
                .map(node -> (JmmNode) node);

        if (argsNode.isEmpty()) {
            return List.of();
        }

        var argsClause = argsNode.get();
        if (argsClause.getNumChildren() == 0) {
            return List.of();
        }

        return argsClause.getChildren();
    }

    private JmmType getLengthExprType(JmmNode lengthExpr) {
        var unaryTarget = getMisparsedUnaryTarget(lengthExpr);
        if (unaryTarget.isPresent()) {
            var lengthType = getLengthExprType(getExprType(unaryTarget.get().operand()));
            return applyUnaryOperator(unaryTarget.get().operator(), lengthType);
        }

        var targetType = getExprType(lengthExpr.getChild(0));
        return getLengthExprType(targetType);
    }

    private JmmType getLengthExprType(JmmType targetType) {
        if (!targetType.isArray()) {
            throw new RuntimeException("Cannot access 'length' on non-array type '" + targetType.print() + "'");
        }

        return intType();
    }

    private JmmType getThisExprType(JmmNode thisExpr) {
        var methodDecl = thisExpr.getAncestor(METHOD_DECL);
        if (methodDecl.isPresent() && methodDecl.get().getBoolean("isStatic", false)) {
            throw new RuntimeException("Cannot use 'this' in a static method");
        }

        return JmmClassType.ofInstance(table.getFullyQualifiedName(), false);
    }

    private JmmType getFieldAccessType(JmmNode fieldAccessExpr) {
        var unaryTarget = getMisparsedUnaryTarget(fieldAccessExpr);
        if (unaryTarget.isPresent()) {
            var fieldType = getFieldAccessType(fieldAccessExpr, getExprType(unaryTarget.get().operand()));
            return applyUnaryOperator(unaryTarget.get().operator(), fieldType);
        }

        var targetType = getExprType(fieldAccessExpr.getChild(0));
        return getFieldAccessType(fieldAccessExpr, targetType);
    }

    private JmmType getFieldAccessType(JmmNode fieldAccessExpr, JmmType targetType) {
        var fieldName = fieldAccessExpr.get("field");

        if (!targetType.isClass()) {
            throw new RuntimeException("Cannot access field '" + fieldName + "' on non-class type '" + targetType.print() + "'");
        }

        var classType = targetType.asClass();
        if (classType.fullyQualifiedName().equals(table.getFullyQualifiedName())) {
            var localField = table.getField(fieldName);
            if (localField.isPresent()) {
                return localField.get().type();
            }
        }

        var importedTable = table.getImportedSymbolTable(classType.fullyQualifiedName());
        if (importedTable.isPresent()) {
            var importedField = importedTable.get().getField(fieldName);
            if (importedField.isPresent()) {
                return importedField.get().type();
            }
        }

        throw new RuntimeException("Could not resolve field '" + fieldName + "'");
    }

    private Optional<ResolvedMethod> resolveMethod(String className, String methodName, List<JmmType> argTypes) {
        if (className.equals(table.getFullyQualifiedName())) {
            var localMethod = findCompatibleMethod(table.getMethods(methodName), argTypes)
                    .map(method -> new ResolvedMethod(method.returnType(), method.isStatic(), className,
                            method.parameters().stream().map(pt.up.fe.comp.jmm.analysis.table.Symbol::type).toList()));
            if (localMethod.isPresent()) {
                return localMethod;
            }

            var superName = table.getSuperFullyQualifiedName();
            if (superName != null) {
                return resolveMethod(superName, methodName, argTypes);
            }

            return Optional.empty();
        }

        var importedTable = table.getImportedSymbolTable(className);
        if (importedTable.isPresent()) {
            var importedMethod = findCompatibleMethod(importedTable.get().getMethods(methodName), argTypes)
                    .map(method -> new ResolvedMethod(method.returnType(), method.isStatic(),
                            importedTable.get().getFullyQualifiedName(),
                            method.parameters().stream().map(pt.up.fe.comp.jmm.analysis.table.Symbol::type).toList()));
            if (importedMethod.isPresent()) {
                return importedMethod;
            }

            var superName = importedTable.get().getSuperFullyQualifiedName();
            if (superName != null) {
                var inheritedMethod = resolveMethod(superName, methodName, argTypes);
                if (inheritedMethod.isPresent()) {
                    return inheritedMethod;
                }
            }
        }

        return tryResolveExternalMethod(className, methodName, argTypes);
    }

    private Optional<MethodSymbol> findCompatibleMethod(List<MethodSymbol> methods, List<JmmType> argTypes) {
        var compatibleMethods = methods.stream()
                .filter(method -> method.parameters().size() == argTypes.size())
                .filter(method -> areCompatibleArguments(method.parameters(), argTypes))
                .toList();

        if (compatibleMethods.isEmpty()) {
            return Optional.empty();
        }

        return compatibleMethods.stream()
                .filter(method -> method.parameters().stream().map(Symbol::type).toList().equals(argTypes))
                .findFirst()
                .or(() -> Optional.of(compatibleMethods.getFirst()));
    }

    private boolean areCompatibleArguments(List<Symbol> parameters, List<JmmType> argTypes) {
        for (int i = 0; i < parameters.size(); i++) {
            if (!isAssignable(parameters.get(i).type(), argTypes.get(i))) {
                return false;
            }
        }

        return true;
    }

    private Optional<MisparsedUnaryTarget> getMisparsedUnaryTarget(JmmNode postfixExpr) {
        if (postfixExpr.getNumChildren() == 0) {
            return Optional.empty();
        }

        var targetExpr = postfixExpr.getChild(0);
        if (!UNARY_EXPR.check(targetExpr) || targetExpr.getNumChildren() == 0) {
            return Optional.empty();
        }

        return Optional.of(new MisparsedUnaryTarget(targetExpr.get("op"), targetExpr.getChild(0)));
    }

    private JmmType applyUnaryOperator(String operator, JmmType operandType) {
        return switch (operator) {
            case "!" -> {
                if (!operandType.equals(JmmPrimitiveType.BOOLEAN)) {
                    throw new RuntimeException("Operator '!' expects a boolean operand");
                }
                yield JmmPrimitiveType.BOOLEAN;
            }
            case "+", "-", "++", "--" -> {
                if (!operandType.equals(intType())) {
                    throw new RuntimeException("Operator '" + operator + "' expects an integer operand");
                }
                yield intType();
            }
            default -> throw new RuntimeException("Unknown unary operator '" + operator + "'");
        };
    }

    private JmmClassType resolveClassType(String name) {
        if (name.equals(table.getClassName())) {
            return JmmClassType.ofInstance(table.getFullyQualifiedName(), false);
        }

        var imported = table.getImportedFullyQualifiedName(name);
        if (imported.isPresent()) {
            return JmmClassType.ofInstance(imported.get(), true);
        }

        var implicit = table.getImplicitImport(name);
        if (implicit.isPresent()) {
            return JmmClassType.ofInstance(implicit.get().getFullyQualifiedName(), true);
        }

        return JmmClassType.ofInstance(name, false);
    }

    private Optional<ResolvedMethod> tryResolveExternalMethod(String className, String methodName, List<JmmType> argTypes) {
        var externalClass = tryLoadExternalClass(className);
        if (externalClass.isEmpty()) {
            return Optional.empty();
        }

        var compatibleMethods = java.util.Arrays.stream(externalClass.get().getMethods())
                .filter(method -> method.getName().equals(methodName))
                .filter(method -> matchesExternalMethod(method, argTypes))
                .toList();

        if (compatibleMethods.isEmpty()) {
            return Optional.empty();
        }

        var exactMethod = compatibleMethods.stream()
                .filter(method -> matchesExactExternalMethod(method, argTypes))
                .findFirst()
                .orElse(compatibleMethods.getFirst());

        return Optional.of(new ResolvedMethod(
                convertReflectionType(exactMethod.getReturnType()),
                Modifier.isStatic(exactMethod.getModifiers()),
                exactMethod.getDeclaringClass().getName(),
                java.util.Arrays.stream(exactMethod.getParameterTypes()).map(this::convertReflectionType).toList()));
    }

    private Optional<Class<?>> tryLoadExternalClass(String className) {
        try {
            return Optional.of(Class.forName(className));
        } catch (ClassNotFoundException e) {
            return Optional.empty();
        }
    }

    private boolean matchesExternalMethod(Method method, List<JmmType> argTypes) {
        var parameterTypes = method.getParameterTypes();
        if (parameterTypes.length != argTypes.size()) {
            return false;
        }

        for (int i = 0; i < parameterTypes.length; i++) {
            if (!isCompatibleExternalType(parameterTypes[i], argTypes.get(i))) {
                return false;
            }
        }

        return true;
    }

    private boolean matchesExactExternalMethod(Method method, List<JmmType> argTypes) {
        var parameterTypes = method.getParameterTypes();
        if (parameterTypes.length != argTypes.size()) {
            return false;
        }

        for (int i = 0; i < parameterTypes.length; i++) {
            if (!convertReflectionType(parameterTypes[i]).equals(argTypes.get(i))) {
                return false;
            }
        }

        return true;
    }

    private boolean isCompatibleExternalType(Class<?> parameterType, JmmType argType) {
        var reflectedType = convertReflectionType(parameterType);
        if (reflectedType.equals(argType)) {
            return true;
        }

        if (parameterType.isPrimitive()) {
            return false;
        }

        if (argType.isPrimitive() || argType.isArray()) {
            return false;
        }

        // If parameter expects Object, any class type is compatible
        if (parameterType == Object.class) {
            return true;
        }

        var argClass = tryLoadExternalClass(argType.asClass().fullyQualifiedName());
        return argClass.filter(parameterType::isAssignableFrom).isPresent();
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

        var externalClass = tryLoadExternalClass(className);
        if (externalClass.isPresent()) {
            var superClass = externalClass.get().getSuperclass();
            return superClass == null ? null : superClass.getName();
        }

        return null;
    }

    private void normalizeStaticMethodTarget(JmmNode methodCallExpr, String ownerClassName) {
        if (methodCallExpr.getNumChildren() == 0) {
            return;
        }

        var currentTarget = methodCallExpr.getChild(0);
        if (VAR_REF_EXPR.check(currentTarget) && currentTarget.get("name").equals(simpleName(ownerClassName))) {
            return;
        }

        var newTarget = new JmmNodeImpl(VAR_REF_EXPR);
        newTarget.put("name", simpleName(ownerClassName));
        newTarget.putObject("forcedStaticType", ownerClassName);
        copyLocation(currentTarget, newTarget);

        methodCallExpr.setChild(newTarget, 0);
        methodCallExpr.putObject("target", newTarget);
    }

    private void copyLocation(JmmNode source, JmmNode target) {
        for (var attribute : List.of("lineStart", "colStart", "lineEnd", "colEnd")) {
            source.getOptionalObject(attribute).ifPresent(value -> target.putObject(attribute, value));
        }
    }

    private String simpleName(String fullyQualifiedName) {
        if (!fullyQualifiedName.contains(".")) {
            return fullyQualifiedName;
        }

        return fullyQualifiedName.substring(fullyQualifiedName.lastIndexOf('.') + 1);
    }

    public Optional<List<JmmType>> getMethodParamTypes(JmmType targetType, String methodName, List<JmmType> argTypes) {
        if (!targetType.isClass()) {
            return Optional.empty();
        }
        var classType = targetType.asClass();
        return resolveMethod(classType.fullyQualifiedName(), methodName, argTypes)
                .map(ResolvedMethod::parameterTypes);
    }

    public Optional<List<JmmType>> getImplicitMethodParamTypes(String methodName, List<JmmType> argTypes) {
        return resolveMethod(table.getFullyQualifiedName(), methodName, argTypes)
                .map(ResolvedMethod::parameterTypes);
    }

}
