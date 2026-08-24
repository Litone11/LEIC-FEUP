package pt.up.fe.comp2026.optimization;

import pt.up.fe.comp.jmm.analysis.table.MethodSymbol;
import pt.up.fe.comp.jmm.analysis.table.SymbolTable;
import pt.up.fe.comp.jmm.analysis.table.type.JmmType;
import pt.up.fe.comp.jmm.analysis.table.type.impls.JmmArrayType;
import pt.up.fe.comp.jmm.analysis.table.type.impls.JmmClassType;
import pt.up.fe.comp.jmm.analysis.table.type.impls.JmmPrimitiveType;
import pt.up.fe.comp.jmm.ast.AJmmVisitor;
import pt.up.fe.comp.jmm.ast.JmmNode;
import pt.up.fe.comp2026.ast.TypeUtils;
import pt.up.fe.comp2026.symboltable.JmmSymbolTable;

import java.util.stream.Collectors;

import static pt.up.fe.comp2026.jmm.ast.JmmKind.*;

/**
 * Generates OLLIR code from JmmNodes that are expressions.
 */
public class OllirExprGeneratorVisitor extends AJmmVisitor<Void, OllirExprResult> {

    private static final String SPACE = " ";
    private static final String ASSIGN = ":=";
    private static final String END_STMT = ";\n";

    private final SymbolTable table;
    private final TypeUtils types;
    private final OptUtils ollirTypes;
    private MethodSymbol currentMethod;

    public OllirExprGeneratorVisitor(SymbolTable table, OptUtils ollirTypes) {
        this.table = table;
        this.types = new TypeUtils(table);
        this.ollirTypes = ollirTypes;
        this.currentMethod = null;
    }

    public void setCurrentMethod(MethodSymbol method) {
        this.currentMethod = method;
    }

    @Override
    protected void buildVisitor() {
        addVisit(INTEGER_LITERAL, this::visitInteger);
        addVisit(BOOLEAN_LITERAL, this::visitBoolean);
        addVisit(VAR_REF_EXPR, this::visitVarRef);
        addVisit(BINARY_EXPR, this::visitBinExpr);
        addVisit(UNARY_EXPR, this::visitUnaryExpr);
        addVisit(PAREN_EXPR, this::visitParenExpr);
        addVisit(THIS_EXPR, this::visitThisExpr);
        addVisit(NEW_OBJECT_EXPR, this::visitNewObject);
        addVisit(METHOD_CALL_EXPR, this::visitMethodCall);
        addVisit(IMPLICIT_THIS_CALL_EXPR, this::visitImplicitThisCall);
        addVisit(FIELD_ACCESS_EXPR, this::visitFieldAccess);
        addVisit(ARRAY_ACCESS_EXPR, this::visitArrayAccess);
        addVisit(NEW_ARRAY_EXPR, this::visitNewArray);
        addVisit(ARRAY_INIT_EXPR, this::visitArrayInit);
        addVisit(LENGTH_EXPR, this::visitLength);
    }

    // ---- Literals ----

    private OllirExprResult visitInteger(JmmNode node, Void unused) {
        return new OllirExprResult(node.get("value") + ".i32");
    }

    private OllirExprResult visitBoolean(JmmNode node, Void unused) {
        String val = node.get("value").equals("true") ? "1" : "0";
        return new OllirExprResult(val + ".bool");
    }

    // ---- Variable reference ----

    private OllirExprResult visitVarRef(JmmNode node, Void unused) {
        var name = node.get("name");
        JmmType type = types.getExprType(node);
        String ollirType = ollirTypes.toOllirType(type);

        // Static reference (class name used as expression)
        if (type instanceof JmmClassType ct && ct.staticRef()) {
            return new OllirExprResult(ollirTypes.sanitizeId(name) + ollirType);
        }

        // Check if local or param
        if (isLocalOrParam(name)) {
            return new OllirExprResult(ollirTypes.sanitizeId(name) + ollirType);
        }

        // Field access via getfield
        if (table.getField(name).isPresent()) {
            return generateGetField(name, type, ollirType);
        }

        return new OllirExprResult(ollirTypes.sanitizeId(name) + ollirType);
    }

    private OllirExprResult generateGetField(String fieldName, JmmType type, String ollirType) {
        var computation = new StringBuilder();
        var tmp = ollirTypes.nextTemp();
        var thisType = ollirTypes.toOllirType(JmmClassType.ofInstance(table.getFullyQualifiedName(), false));

        computation.append(tmp).append(ollirType).append(SPACE)
                .append(ASSIGN).append(ollirType).append(SPACE)
                .append("getfield(this").append(thisType)
                .append(", ").append(ollirTypes.sanitizeId(fieldName)).append(ollirType)
                .append(")").append(ollirType).append(END_STMT);

        return new OllirExprResult(tmp + ollirType, computation);
    }

    // ---- Binary expressions ----

    private OllirExprResult visitBinExpr(JmmNode node, Void unused) {
        var op = node.get("op");

        // Short-circuit operators
        if (op.equals("&&")) return visitAndExpr(node);
        if (op.equals("||")) return visitOrExpr(node);

        var lhs = visit(node.getChild(0));
        var rhs = visit(node.getChild(1));

        var computation = new StringBuilder();
        computation.append(lhs.getComputation());
        computation.append(rhs.getComputation());

        JmmType resType = types.getExprType(node);
        String resOllirType = ollirTypes.toOllirType(resType);

        // For comparisons, operand type differs from result type
        JmmType lhsType = types.getExprType(node.getChild(0));
        String opType = ollirTypes.toOllirType(lhsType);

        var tmp = ollirTypes.nextTemp();
        String code = tmp + resOllirType;

        computation.append(code).append(SPACE)
                .append(ASSIGN).append(resOllirType).append(SPACE)
                .append(lhs.getCode()).append(SPACE)
                .append(op).append(opType).append(SPACE)
                .append(rhs.getCode()).append(END_STMT);

        return new OllirExprResult(code, computation);
    }

    private OllirExprResult visitAndExpr(JmmNode node) {
        var lhs = visit(node.getChild(0));
        var computation = new StringBuilder();

        var andRhsLabel = ollirTypes.nextTemp("andRhs");
        var andTrueLabel = ollirTypes.nextTemp("andTrue");
        var andEndLabel = ollirTypes.nextTemp("andEnd");
        var tmp = ollirTypes.nextTemp();

        computation.append(lhs.getComputation());
        computation.append("if (").append(lhs.getCode()).append(") goto ").append(andRhsLabel).append(END_STMT);
        computation.append(tmp).append(".bool :=.bool 0.bool;\n");
        computation.append("goto ").append(andEndLabel).append(END_STMT);

        computation.append(andRhsLabel).append(":\n");
        var rhs = visit(node.getChild(1));
        computation.append(rhs.getComputation());
        computation.append("if (").append(rhs.getCode()).append(") goto ").append(andTrueLabel).append(END_STMT);
        computation.append(tmp).append(".bool :=.bool 0.bool;\n");
        computation.append("goto ").append(andEndLabel).append(END_STMT);

        computation.append(andTrueLabel).append(":\n");
        computation.append(tmp).append(".bool :=.bool 1.bool;\n");
        computation.append(andEndLabel).append(":\n");

        return new OllirExprResult(tmp + ".bool", computation);
    }

    private OllirExprResult visitOrExpr(JmmNode node) {
        var lhs = visit(node.getChild(0));
        var computation = new StringBuilder();

        var orTrueLabel = ollirTypes.nextTemp("orTrue");
        var orFalseLabel = ollirTypes.nextTemp("orFalse");
        var orEndLabel = ollirTypes.nextTemp("orEnd");
        var tmp = ollirTypes.nextTemp();

        computation.append(lhs.getComputation());
        computation.append("if (").append(lhs.getCode()).append(") goto ").append(orTrueLabel).append(END_STMT);

        var rhs = visit(node.getChild(1));
        computation.append(rhs.getComputation());
        computation.append("if (").append(rhs.getCode()).append(") goto ").append(orTrueLabel).append(END_STMT);

        computation.append(orFalseLabel).append(":\n");
        computation.append(tmp).append(".bool :=.bool 0.bool;\n");
        computation.append("goto ").append(orEndLabel).append(END_STMT);

        computation.append(orTrueLabel).append(":\n");
        computation.append(tmp).append(".bool :=.bool 1.bool;\n");
        computation.append(orEndLabel).append(":\n");

        return new OllirExprResult(tmp + ".bool", computation);
    }

    // ---- Unary expressions ----

    private OllirExprResult visitUnaryExpr(JmmNode node, Void unused) {
        var op = node.get("op");
        var operand = visit(node.getChild(0));
        if (op.equals("+")) {
            return operand;
        }

        var computation = new StringBuilder();
        computation.append(operand.getComputation());

        JmmType resType = types.getExprType(node);
        String resOllirType = ollirTypes.toOllirType(resType);
        var tmp = ollirTypes.nextTemp();
        String code = tmp + resOllirType;

        switch (op) {
            case "!" -> computation.append(code).append(SPACE)
                    .append(ASSIGN).append(resOllirType).append(SPACE)
                    .append("!").append(resOllirType).append(SPACE)
                    .append(operand.getCode()).append(END_STMT);
            case "-" -> computation.append(code).append(SPACE)
                    .append(ASSIGN).append(resOllirType).append(SPACE)
                    .append("0.i32 -.i32 ").append(operand.getCode()).append(END_STMT);
            case "++", "--" -> {
                // For prefix ++ and --, compute the new value and write it back to
                // the operand's lvalue (local/param or field). The expression value
                // is the incremented result.
                var delta = op.equals("--") ? "-" : "+";
                computation.append(code).append(SPACE)
                        .append(ASSIGN).append(resOllirType).append(SPACE)
                        .append(operand.getCode()).append(" ").append(delta).append(".i32 1.i32").append(END_STMT);
                emitStore(node.getChild(0), code, computation);
            }
            default -> computation.append(code).append(SPACE)
                    .append(ASSIGN).append(resOllirType).append(SPACE)
                    .append(op).append(resOllirType).append(SPACE)
                    .append(operand.getCode()).append(END_STMT);
        }

        return new OllirExprResult(code, computation);
    }

    // ---- Paren ----

    private OllirExprResult visitParenExpr(JmmNode node, Void unused) {
        return visit(node.getChild(0));
    }

    // ---- This ----

    private OllirExprResult visitThisExpr(JmmNode node, Void unused) {
        var thisType = "." + ollirTypes.simpleName(table.getFullyQualifiedName());
        return new OllirExprResult("this" + thisType);
    }

    // ---- New object ----

    private OllirExprResult visitNewObject(JmmNode node, Void unused) {
        var className = node.get("name");
        var fullClassName = resolveClassName(className);
        var simpleName = ollirTypes.simpleName(fullClassName);
        var ollirType = "." + simpleName;

        var computation = new StringBuilder();
        var tmp = ollirTypes.nextTemp();
        String code = tmp + ollirType;

        computation.append(code).append(SPACE)
                .append(ASSIGN).append(ollirType).append(SPACE)
                .append("new(").append(simpleName).append(")").append(ollirType)
                .append(END_STMT);

        // invokespecial for constructor
        var argsNode = node.getOptionalObject("argsNode").map(o -> (JmmNode) o);
        var argsCode = buildArgsCode(computation, argsNode, node);

        computation.append("invokespecial(").append(code)
                .append(", \"<init>\"").append(argsCode).append(").V").append(END_STMT);

        return new OllirExprResult(code, computation);
    }

    // ---- Method calls ----

    private OllirExprResult visitMethodCall(JmmNode node, Void unused) {
        var unaryTarget = getMisparsedUnaryTarget(node);
        if (unaryTarget.isPresent()) {
            var baseCall = visitMethodCall(node, unaryTarget.get().operand());
            return applyUnaryToPostfixResult(unaryTarget.get().operator(), node, baseCall);
        }

        return visitMethodCall(node, node.getChild(0));
    }

    private OllirExprResult visitMethodCall(JmmNode node, JmmNode targetNode) {
        var methodName = node.get("method");
        var targetType = types.getExprType(targetNode);

        JmmType retType = types.getExprType(node);
        String retOllirType = ollirTypes.toOllirType(retType);

        var computation = new StringBuilder();

        var argsNode = node.getOptionalObject("argsNode").map(o -> (JmmNode) o);
        var argTypes = types.getArgumentNodes(node).stream()
                .map(types::getExprType)
                .toList();
        var expectedParamTypes = types.getMethodParamTypes(targetType, methodName, argTypes).orElse(null);
        var argsCode = buildArgsCode(computation, argsNode, node, expectedParamTypes);

        String callInstr;
        if (targetType instanceof JmmClassType ct && ct.staticRef()) {
            // Static call
            callInstr = "invokestatic(" + ollirTypes.simpleName(ct.fullyQualifiedName())
                    + ", \"" + methodName + "\"" + argsCode + ")" + retOllirType;
        } else {
            // Virtual call
            var target = visit(targetNode);
            computation.append(target.getComputation());
            callInstr = "invokevirtual(" + target.getCode()
                    + ", \"" + methodName + "\"" + argsCode + ")" + retOllirType;
        }

        if (retType.equals(JmmPrimitiveType.VOID)) {
            computation.append(callInstr).append(END_STMT);
            return new OllirExprResult("", computation);
        } else {
            var tmp = ollirTypes.nextTemp();
            String code = tmp + retOllirType;
            computation.append(code).append(SPACE)
                    .append(ASSIGN).append(retOllirType).append(SPACE)
                    .append(callInstr).append(END_STMT);
            return new OllirExprResult(code, computation);
        }
    }

    private OllirExprResult visitImplicitThisCall(JmmNode node, Void unused) {
        var methodName = node.get("method");
        JmmType retType = types.getExprType(node);
        String retOllirType = ollirTypes.toOllirType(retType);
        var thisType = "." + ollirTypes.simpleName(table.getFullyQualifiedName());

        var computation = new StringBuilder();
        var argsNode = node.getOptionalObject("argsNode").map(o -> (JmmNode) o);
        var argTypes = types.getArgumentNodes(node).stream()
                .map(types::getExprType)
                .toList();
        var expectedParamTypes = types.getImplicitMethodParamTypes(methodName, argTypes).orElse(null);
        var argsCode = buildArgsCode(computation, argsNode, node, expectedParamTypes);

        String callInstr = "invokevirtual(this" + thisType + ", \"" + methodName + "\"" + argsCode + ")" + retOllirType;

        if (retType.equals(JmmPrimitiveType.VOID)) {
            computation.append(callInstr).append(END_STMT);
            return new OllirExprResult("", computation);
        } else {
            var tmp = ollirTypes.nextTemp();
            String code = tmp + retOllirType;
            computation.append(code).append(SPACE)
                    .append(ASSIGN).append(retOllirType).append(SPACE)
                    .append(callInstr).append(END_STMT);
            return new OllirExprResult(code, computation);
        }
    }

    // ---- Field access ----

    private OllirExprResult visitFieldAccess(JmmNode node, Void unused) {
        var unaryTarget = getMisparsedUnaryTarget(node);
        if (unaryTarget.isPresent()) {
            var baseAccess = visitFieldAccess(node, unaryTarget.get().operand());
            return applyUnaryToPostfixResult(unaryTarget.get().operator(), node, baseAccess);
        }

        return visitFieldAccess(node, node.getChild(0));
    }

    private OllirExprResult visitFieldAccess(JmmNode node, JmmNode targetNode) {
        var fieldName = node.get("field");
        JmmType fieldType = types.getExprType(node);
        String fieldOllirType = ollirTypes.toOllirType(fieldType);

        var target = visit(targetNode);
        var computation = new StringBuilder();
        computation.append(target.getComputation());

        var tmp = ollirTypes.nextTemp();
        String code = tmp + fieldOllirType;

        computation.append(code).append(SPACE)
                .append(ASSIGN).append(fieldOllirType).append(SPACE)
                .append("getfield(").append(target.getCode())
                .append(", ").append(ollirTypes.sanitizeId(fieldName)).append(fieldOllirType)
                .append(")").append(fieldOllirType).append(END_STMT);

        return new OllirExprResult(code, computation);
    }

    // ---- Array access ----

    private OllirExprResult visitArrayAccess(JmmNode node, Void unused) {
        var unaryTarget = getMisparsedUnaryTarget(node);
        if (unaryTarget.isPresent()) {
            var op = unaryTarget.get().operator();
            if (op.equals("++") || op.equals("--")) {
                return incDecArrayAccess(node, unaryTarget.get().operand(), op);
            }
            var baseAccess = visitArrayAccess(node, unaryTarget.get().operand());
            return applyUnaryToPostfixResult(op, node, baseAccess);
        }

        return visitArrayAccess(node, node.getChild(0));
    }

    // Handles ++/-- on an array element (e.g. ++a[i]): read the element, increment
    // it, and write it back, evaluating the array reference and index only once.
    private OllirExprResult incDecArrayAccess(JmmNode node, JmmNode arrayNode, String op) {
        var indexNode = node.getChild(1);

        JmmType elemType = types.getExprType(node);
        String elemOllirType = ollirTypes.toOllirType(elemType);

        var array = visit(arrayNode);
        var index = visit(indexNode);

        var computation = new StringBuilder();
        computation.append(array.getComputation());
        computation.append(index.getComputation());

        String arrElem = array.getCode() + "[" + index.getCode() + "]" + elemOllirType;

        var tmp = ollirTypes.nextTemp();
        String code = tmp + elemOllirType;

        // read: tmp := arr[idx]
        computation.append(code).append(SPACE)
                .append(ASSIGN).append(elemOllirType).append(SPACE)
                .append(arrElem).append(END_STMT);
        // increment: tmp := tmp +/- 1
        var delta = op.equals("--") ? "-" : "+";
        computation.append(code).append(SPACE)
                .append(ASSIGN).append(elemOllirType).append(SPACE)
                .append(code).append(" ").append(delta).append(".i32 1.i32").append(END_STMT);
        // write back: arr[idx] := tmp
        computation.append(arrElem).append(SPACE)
                .append(ASSIGN).append(elemOllirType).append(SPACE)
                .append(code).append(END_STMT);

        return new OllirExprResult(code, computation);
    }

    private OllirExprResult visitArrayAccess(JmmNode node, JmmNode arrayNode) {
        var indexNode = node.getChild(1);

        JmmType elemType = types.getExprType(node);
        String elemOllirType = ollirTypes.toOllirType(elemType);

        var array = visit(arrayNode);
        var index = visit(indexNode);

        var computation = new StringBuilder();
        computation.append(array.getComputation());
        computation.append(index.getComputation());

        var tmp = ollirTypes.nextTemp();
        String code = tmp + elemOllirType;

        computation.append(code).append(SPACE)
                .append(ASSIGN).append(elemOllirType).append(SPACE)
                .append(array.getCode())
                .append("[").append(index.getCode()).append("]")
                .append(elemOllirType).append(END_STMT);

        return new OllirExprResult(code, computation);
    }

    // ---- New array ----

    private OllirExprResult visitNewArray(JmmNode node, Void unused) {
        JmmType arrayType = types.getExprType(node);
        String arrayOllirType = ollirTypes.toOllirType(arrayType);

        var computation = new StringBuilder();
        var sizesCode = new StringBuilder();

        for (var sizeNode : node.getChildren()) {
            var size = visit(sizeNode);
            computation.append(size.getComputation());
            if (!sizesCode.isEmpty()) {
                sizesCode.append(", ");
            }
            sizesCode.append(size.getCode());
        }

        var tmp = ollirTypes.nextTemp();
        String code = tmp + arrayOllirType;

        computation.append(code).append(SPACE)
                .append(ASSIGN).append(arrayOllirType).append(SPACE)
                .append("new(array, ").append(sizesCode).append(")").append(arrayOllirType)
                .append(END_STMT);

        return new OllirExprResult(code, computation);
    }

    // ---- Array initializer ----

    private OllirExprResult visitArrayInit(JmmNode node, Void unused) {
        JmmType arrayType = types.getExprType(node);
        String arrayOllirType = ollirTypes.toOllirType(arrayType);

        var argsClause = node.getOptionalObject("argsNode").map(o -> (JmmNode) o);
        var elements = argsClause.map(JmmNode::getChildren).orElse(java.util.List.of());
        int size = elements.size();

        var computation = new StringBuilder();
        var tmp = ollirTypes.nextTemp();
        String code = tmp + arrayOllirType;

        computation.append(code).append(SPACE)
                .append(ASSIGN).append(arrayOllirType).append(SPACE)
                .append("new(array, ").append(size).append(".i32)").append(arrayOllirType)
                .append(END_STMT);

        String elemOllirType = arrayType.isArray()
                ? ollirTypes.toOllirType(arrayType.asArray().itemType()) : ".i32";

        for (int i = 0; i < elements.size(); i++) {
            var elem = visit(elements.get(i));
            computation.append(elem.getComputation());
            computation.append(code)
                    .append("[").append(i).append(".i32]").append(elemOllirType)
                    .append(SPACE).append(ASSIGN).append(elemOllirType).append(SPACE)
                    .append(elem.getCode()).append(END_STMT);
        }

        return new OllirExprResult(code, computation);
    }

    // ---- Length ----

    private OllirExprResult visitLength(JmmNode node, Void unused) {
        var unaryTarget = getMisparsedUnaryTarget(node);
        if (unaryTarget.isPresent()) {
            var baseLength = visitLength(node, unaryTarget.get().operand());
            return applyUnaryToPostfixResult(unaryTarget.get().operator(), node, baseLength);
        }

        return visitLength(node, node.getChild(0));
    }

    private OllirExprResult visitLength(JmmNode node, JmmNode arrayNode) {
        var array = visit(arrayNode);

        var computation = new StringBuilder();
        computation.append(array.getComputation());

        var tmp = ollirTypes.nextTemp();
        String code = tmp + ".i32";

        computation.append(code).append(SPACE)
                .append(ASSIGN).append(".i32 ")
                .append("arraylength(").append(array.getCode()).append(").i32")
                .append(END_STMT);

        return new OllirExprResult(code, computation);
    }

    // ---- Helpers ----

    private boolean isLocalOrParam(String name) {
        if (currentMethod == null) return false;
        return currentMethod.getLocalVariable(name).isPresent()
                || currentMethod.getParameter(name).isPresent();
    }

    private String resolveClassName(String simpleName) {
        var imported = table.getImportedFullyQualifiedName(simpleName);
        return imported.orElse(simpleName);
    }

    /**
     * Builds args string " , arg1.T1, arg2.T2" (leading comma+space) for method calls.
     * Also appends computation for each arg to the provided StringBuilder.
     */
    private String buildArgsCode(StringBuilder computation, java.util.Optional<JmmNode> argsNodeOpt, JmmNode context) {
        return buildArgsCode(computation, argsNodeOpt, context, null);
    }

    private String buildArgsCode(StringBuilder computation, java.util.Optional<JmmNode> argsNodeOpt, JmmNode context, java.util.List<JmmType> expectedParamTypes) {
        if (argsNodeOpt.isEmpty()) return "";
        var argsClause = argsNodeOpt.get();
        if (argsClause.getNumChildren() == 0) return "";

        var args = argsClause.getChildren();
        var argsCode = new StringBuilder();
        for (int i = 0; i < args.size(); i++) {
            var arg = args.get(i);
            var argResult = visit(arg);
            computation.append(argResult.getComputation());
            
            String argCode = argResult.getCode();
            if (expectedParamTypes != null && i < expectedParamTypes.size()) {
                var expectedType = expectedParamTypes.get(i);
                var expectedOllirType = ollirTypes.toOllirType(expectedType);
                // The value code is "<name><ollirType>"; the OLLIR type starts at the first
                // '.' (names/literals never contain dots). Use indexOf, not lastIndexOf, so
                // multi-component types like ".array.i32" are replaced whole, not doubled.
                int firstDot = argCode.indexOf('.');
                String base = firstDot >= 0 ? argCode.substring(0, firstDot) : argCode;
                argCode = base + expectedOllirType;
            }
            argsCode.append(", ").append(argCode);
        }
        return argsCode.toString();
    }

    private OllirExprResult applyUnaryToPostfixResult(String operator, JmmNode context, OllirExprResult operand) {
        if (operator.equals("+")) {
            return operand;
        }

        var computation = new StringBuilder();
        computation.append(operand.getComputation());

        var resultType = types.getExprType(context);
        var resultOllirType = ollirTypes.toOllirType(resultType);
        var tmp = ollirTypes.nextTemp();
        var code = tmp + resultOllirType;

        switch (operator) {
            case "-" -> computation.append(code).append(SPACE)
                    .append(ASSIGN).append(resultOllirType).append(SPACE)
                    .append("0.i32 -.i32 ").append(operand.getCode()).append(END_STMT);
            case "!" -> computation.append(code).append(SPACE)
                    .append(ASSIGN).append(resultOllirType).append(SPACE)
                    .append("!").append(resultOllirType).append(SPACE)
                    .append(operand.getCode()).append(END_STMT);
            case "++", "--" -> {
                var delta = operator.equals("--") ? "-" : "+";
                computation.append(code).append(SPACE)
                        .append(ASSIGN).append(resultOllirType).append(SPACE)
                        .append(operand.getCode()).append(" ").append(delta).append(".i32 1.i32").append(END_STMT);
            }
            default -> throw new RuntimeException("Unsupported unary operator '" + operator + "'");
        }

        return new OllirExprResult(code, computation);
    }

    // Writes valueCode back into the lvalue denoted by the given node (a local/param
    // variable or a field). Used for ++/-- side effects. No-op for non-lvalues.
    private void emitStore(JmmNode lvalue, String valueCode, StringBuilder computation) {
        var target = lvalue;
        while (PAREN_EXPR.check(target) && target.getNumChildren() > 0) {
            target = target.getChild(0);
        }

        if (!VAR_REF_EXPR.check(target)) {
            return;
        }

        var name = target.get("name");
        JmmType type = types.getExprType(target);
        String ollirType = ollirTypes.toOllirType(type);

        if (isLocalOrParam(name)) {
            computation.append(ollirTypes.sanitizeId(name)).append(ollirType).append(SPACE)
                    .append(ASSIGN).append(ollirType).append(SPACE)
                    .append(valueCode).append(END_STMT);
        } else if (table.getField(name).isPresent()) {
            var thisType = ollirTypes.toOllirType(JmmClassType.ofInstance(table.getFullyQualifiedName(), false));
            computation.append("putfield(this").append(thisType).append(", ")
                    .append(ollirTypes.sanitizeId(name)).append(ollirType).append(", ")
                    .append(valueCode).append(").V").append(END_STMT);
        }
    }

    private java.util.Optional<MisparsedUnaryTarget> getMisparsedUnaryTarget(JmmNode postfixExpr) {
        if (postfixExpr.getNumChildren() == 0) {
            return java.util.Optional.empty();
        }

        var targetExpr = postfixExpr.getChild(0);
        if (!UNARY_EXPR.check(targetExpr) || targetExpr.getNumChildren() == 0) {
            return java.util.Optional.empty();
        }

        return java.util.Optional.of(new MisparsedUnaryTarget(targetExpr.get("op"), targetExpr.getChild(0)));
    }

    private record MisparsedUnaryTarget(String operator, JmmNode operand) {
    }
}
