package pt.up.fe.comp2026.optimization;

import pt.up.fe.comp.jmm.analysis.table.MethodSymbol;
import pt.up.fe.comp.jmm.analysis.table.Symbol;
import pt.up.fe.comp.jmm.analysis.table.SymbolTable;
import pt.up.fe.comp.jmm.analysis.table.type.JmmType;
import pt.up.fe.comp.jmm.analysis.table.type.impls.JmmPrimitiveType;
import pt.up.fe.comp.jmm.ast.AJmmVisitor;
import pt.up.fe.comp.jmm.ast.JmmNode;
import pt.up.fe.comp2026.ast.NodeUtils;
import pt.up.fe.comp2026.ast.TypeUtils;
import pt.up.fe.comp2026.jmm.ast.JmmAttributes;

import java.util.stream.Collectors;

import static pt.up.fe.comp2026.jmm.ast.JmmKind.*;

/**
 * Generates OLLIR code from JmmNodes that are not expressions.
 */
public class OllirGeneratorVisitor extends AJmmVisitor<Void, String> {

    private static final String SPACE = " ";
    private static final String ASSIGN = ":=";
    private static final String END_STMT = ";\n";
    private static final String NL = "\n";
    private static final String L_BRACKET = " {\n";
    private static final String R_BRACKET = "}\n";

    private final SymbolTable table;
    private final TypeUtils types;
    private final OptUtils ollirTypes;
    private final OllirExprGeneratorVisitor exprVisitor;
    private MethodSymbol currentMethod;

    public OllirGeneratorVisitor(SymbolTable table) {
        this.table = table;
        this.types = new TypeUtils(table);
        this.ollirTypes = new OptUtils(types);
        exprVisitor = new OllirExprGeneratorVisitor(table, ollirTypes);
        currentMethod = null;
    }

    @Override
    protected void buildVisitor() {
        addVisit(PROGRAM, this::visitProgram);
        addVisit(PACKAGE_DECL, this::visitPackageDecl);
        addVisit(IMPORT_DECL, this::visitImportDecl);
        addVisit(CLASS_DECL, this::visitClass);
        addVisit(METHOD_DECL, this::visitMethodDecl);
        addVisit(PARAM, this::visitParam);
        addVisit(COMPOUND_STMT, this::visitCompoundStmt);
        addVisit(EXPR_STMT, this::visitExprStmt);
        addVisit(RETURN_STMT, this::visitReturn);
        addVisit(ASSIGN_STMT, this::visitAssignStmt);
        addVisit(ARRAY_ASSIGN_STMT, this::visitArrayAssignStmt);
        addVisit(IF_ELSE_STMT, this::visitIfElseStmt);
        addVisit(IF_STMT, this::visitIfStmt);
        addVisit(WHILE_STMT, this::visitWhileStmt);
        addVisit(DO_WHILE_STMT, this::visitDoWhileStmt);
        addVisit(FOR_STMT, this::visitForStmt);
        setDefaultVisit(this::defaultVisit);
    }

    private String visitProgram(JmmNode node, Void unused) {
        var code = new StringBuilder();
        node.getChildren().stream().map(this::visit).forEach(code::append);
        return code.toString();
    }

    private String visitPackageDecl(JmmNode node, Void unused) {
        return "package " + String.join(".", node.getObjectAsList("path", String.class)) + ";\n";
    }

    private String visitImportDecl(JmmNode node, Void unused) {
        return "import " + String.join(".", node.getObjectAsList("path", String.class)) + ";\n";
    }

    private String visitClass(JmmNode node, Void unused) {
        var code = new StringBuilder();
        code.append(NL);
        code.append(table.getClassName());

        var superName = table.getSuperFullyQualifiedName();
        if (superName != null && !superName.isBlank()) {
            code.append(" extends ").append(ollirTypes.simpleName(superName));
        } else {
            code.append(" extends Object");
        }

        code.append(L_BRACKET);
        code.append(NL);

        // Fields
        for (var field : table.getFields()) {
            code.append(".field ");
            code.append(ollirTypes.sanitizeId(field.name()));
            code.append(ollirTypes.toOllirType(field.type()));
            code.append(END_STMT);
        }
        code.append(NL);

        code.append(buildConstructor(node));
        code.append(NL);

        for (var child : node.getChildren(METHOD_DECL)) {
            code.append(visit(child));
        }

        code.append(R_BRACKET);
        return code.toString();
    }

    private String buildConstructor(JmmNode classNode) {
        var superFqn = table.getSuperFullyQualifiedName();
        var superClassName = (superFqn != null && !superFqn.isBlank()) ? superFqn : "java.lang.Object";
        var superSimpleName = ollirTypes.simpleName(superClassName);
        var sb = new StringBuilder();
        sb.append(".construct \"<init>\"().V {\n")
          .append("    invokespecial(this.").append(superSimpleName).append(", \"<init>\").V;\n");

        // Generate field initializers
        exprVisitor.setCurrentMethod(null);
        var thisTypeStr = "." + table.getClassName();
        for (var varDecl : classNode.getChildren(VAR_DECL)) {
            var initNodeOpt = varDecl.getOptionalObject("initNode").map(node -> (JmmNode) node);
            if (initNodeOpt.isEmpty()) continue;
            var initNode = initNodeOpt.get();
            if (!WITH_INITIALIZER.check(initNode)) continue;
            if (initNode.getNumChildren() == 0) continue;

            var exprNode = initNode.getChild(0);
            var fieldName = varDecl.get("name");
            var fieldSymbol = table.getFields().stream().filter(f -> f.name().equals(fieldName)).findFirst();
            if (fieldSymbol.isEmpty()) continue;
            var fieldTypeStr = ollirTypes.toOllirType(fieldSymbol.get().type());

            var result = exprVisitor.visit(exprNode);
            sb.append(result.getComputation());
            sb.append("    putfield(this").append(thisTypeStr).append(", ")
              .append(ollirTypes.sanitizeId(fieldName)).append(fieldTypeStr).append(", ")
              .append(result.getCode()).append(").V;\n");
        }

        sb.append("}\n");
        return sb.toString();
    }

    private String visitMethodDecl(JmmNode node, Void unused) {
        currentMethod = table.getMethod(TypeUtils.with(table).getMethodDeclSignature(node)).orElseThrow();
        exprVisitor.setCurrentMethod(currentMethod);

        var code = new StringBuilder(".method ");

        var visibility = node.getOptional("visibility").orElse(null);
        if (visibility != null
                && !visibility.isBlank()
                && !"package-protected".equals(visibility)) {
            code.append(visibility).append(" ");
        } else {
            boolean isPublic = NodeUtils.getBooleanAttribute(node, "isPublic", "false");
            if (isPublic) code.append("public ");
        }

        boolean isStatic = node.getObject("isStatic", Boolean.class);
        if (isStatic) code.append("static ");

        var name = ollirTypes.sanitizeId(node.get("name"));
        code.append(name);

        // params
        var params = currentMethod.parameters();
        var paramsCode = params.stream()
                .map(p -> ollirTypes.sanitizeId(p.name()) + ollirTypes.toOllirType(p.type()))
                .collect(Collectors.joining(", "));
        code.append("(").append(paramsCode).append(")");

        // return type
        var retType = ollirTypes.toOllirType(currentMethod.returnType());
        code.append(retType);
        code.append(L_BRACKET);

        // body statements
        var stmts = node.getChildren(STMT);
        for (var stmt : stmts) {
            var stmtCode = visit(stmt);
            if (stmtCode != null && !stmtCode.isEmpty()) {
                code.append(stmtCode);
            }
        }

        if (JmmPrimitiveType.VOID.equals(currentMethod.returnType())) {
            code.append("ret.V;\n");
        }

        code.append(R_BRACKET);
        code.append(NL);

        currentMethod = null;
        exprVisitor.setCurrentMethod(null);
        return code.toString();
    }

    private String visitParam(JmmNode node, Void unused) {
        return node.get("name") + ollirTypes.toOllirType(
                types.convertType(node.getObject("typeNode", JmmNode.class)));
    }

    private String visitCompoundStmt(JmmNode node, Void unused) {
        var code = new StringBuilder();
        for (var child : node.getChildren(STMT)) {
            code.append(visit(child));
        }
        return code.toString();
    }

    private String visitExprStmt(JmmNode node, Void unused) {
        var expr = node.getChild(0);
        var result = exprVisitor.visit(expr);
        var code = new StringBuilder();
        code.append(result.getComputation());
        // include the code itself if it has side effects (method calls generate code in computation)
        return code.toString();
    }

    private String visitReturn(JmmNode node, Void unused) {
        JmmType retType = currentMethod.returnType();

        // void return with no child
        if (retType.equals(JmmPrimitiveType.VOID)) {
            if (node.getNumChildren() == 0) {
                return "ret.V;\n";
            }
            var expr = exprVisitor.visit(node.getChild(0));
            return expr.getComputation() + "ret.V;\n";
        }

        var expr = exprVisitor.visit(node.getChild(0));
        var code = new StringBuilder();
        code.append(expr.getComputation());
        code.append("ret");
        code.append(ollirTypes.toOllirType(retType));
        code.append(SPACE);
        code.append(expr.getCode());
        code.append(END_STMT);
        return code.toString();
    }

    private String visitAssignStmt(JmmNode node, Void unused) {
        var varName = node.get(JmmAttributes.ASSIGN_STMT.VAR);
        var rhs = exprVisitor.visit(node.getChild(0));

        // Determine type of variable being assigned
        JmmType varType = resolveVarType(varName);
        String typeString = ollirTypes.toOllirType(varType);

        // Check if it's a field assignment
        boolean isField = isFieldVar(varName);

        var code = new StringBuilder();
        code.append(rhs.getComputation());

        if (isField) {
            // putfield(this.ClassName, fieldName.Type, value.Type).V;
            var thisType = ollirTypes.toOllirType(types.getExprType(
                    makeThisNode(node)));
            code.append("putfield(this")
                    .append(thisType)
                    .append(", ")
                    .append(ollirTypes.sanitizeId(varName))
                    .append(typeString)
                    .append(", ")
                    .append(rhs.getCode())
                    .append(").V")
                    .append(END_STMT);
        } else {
            code.append(ollirTypes.sanitizeId(varName))
                    .append(typeString)
                    .append(SPACE)
                    .append(ASSIGN)
                    .append(typeString)
                    .append(SPACE)
                    .append(rhs.getCode())
                    .append(END_STMT);
        }

        return code.toString();
    }

    private String visitArrayAssignStmt(JmmNode node, Void unused) {
        var arrayName = node.get(JmmAttributes.ARRAY_ASSIGN_STMT.ARRAY);
        var children = node.getChildren();
        var indexNodes = children.subList(0, children.size() - 1);
        var valueNode = children.get(children.size() - 1);

        var code = new StringBuilder();

        // Get element type
        JmmType currentArrayType = resolveVarType(arrayName);
        String currentArrayTypeStr = ollirTypes.toOllirType(currentArrayType);

        // Check if array is a field
        boolean isField = isFieldVar(arrayName);

        // Load array ref (may need getfield)
        String arrayCode;
        if (isField) {
            var tmp = ollirTypes.nextTemp();
            var thisType = ollirTypes.toOllirType(types.getExprType(makeThisNode(node)));
            code.append(tmp).append(currentArrayTypeStr).append(SPACE)
                    .append(ASSIGN).append(currentArrayTypeStr).append(SPACE)
                    .append("getfield(this").append(thisType).append(", ")
                    .append(ollirTypes.sanitizeId(arrayName)).append(currentArrayTypeStr)
                    .append(")").append(currentArrayTypeStr).append(END_STMT);
            arrayCode = tmp + currentArrayTypeStr;
        } else {
            arrayCode = ollirTypes.sanitizeId(arrayName) + currentArrayTypeStr;
        }

        for (int i = 0; i < indexNodes.size() - 1; i++) {
            var idx = exprVisitor.visit(indexNodes.get(i));
            code.append(idx.getComputation());

            var nextArrayType = getIndexedArrayType(currentArrayType);
            var nextArrayTypeStr = ollirTypes.toOllirType(nextArrayType);
            var tmp = ollirTypes.nextTemp();
            code.append(tmp).append(nextArrayTypeStr).append(SPACE)
                    .append(ASSIGN).append(nextArrayTypeStr).append(SPACE)
                    .append(arrayCode)
                    .append("[").append(idx.getCode()).append("]")
                    .append(nextArrayTypeStr)
                    .append(END_STMT);

            arrayCode = tmp + nextArrayTypeStr;
            currentArrayType = nextArrayType;
            currentArrayTypeStr = nextArrayTypeStr;
        }

        var lastIndex = exprVisitor.visit(indexNodes.get(indexNodes.size() - 1));
        code.append(lastIndex.getComputation());

        var elemType = currentArrayType.isArray() ? currentArrayType.asArray().itemType() : TypeUtils.intType();
        var elemTypeStr = ollirTypes.toOllirType(elemType);

        var val = exprVisitor.visit(valueNode);
        code.append(val.getComputation());

        code.append(arrayCode)
                .append("[").append(lastIndex.getCode()).append("]")
                .append(elemTypeStr)
                .append(SPACE).append(ASSIGN).append(elemTypeStr).append(SPACE)
                .append(val.getCode())
                .append(END_STMT);

        return code.toString();
    }

    private String visitIfElseStmt(JmmNode node, Void unused) {
        // children: condition(EXPR), thenStmt(STMT), elseStmt(STMT)
        var condExpr = node.getChild(0);
        var thenStmt = node.getChild(1);
        var elseStmt = node.getChild(2);

        var thenLabel = ollirTypes.nextTemp("then");
        var endifLabel = ollirTypes.nextTemp("endif");

        var cond = exprVisitor.visit(condExpr);
        var code = new StringBuilder();

        code.append(cond.getComputation());
        code.append("if (").append(cond.getCode()).append(") goto ").append(thenLabel).append(END_STMT);

        // else body
        code.append(visit(elseStmt));
        code.append("goto ").append(endifLabel).append(END_STMT);

        // then body
        code.append(thenLabel).append(":\n");
        code.append(visit(thenStmt));

        code.append(endifLabel).append(":\n");
        return code.toString();
    }

    private String visitIfStmt(JmmNode node, Void unused) {
        // children: condition(EXPR), thenStmt(STMT)
        var condExpr = node.getChild(0);
        var thenStmt = node.getChild(1);

        var thenLabel = ollirTypes.nextTemp("then");
        var endifLabel = ollirTypes.nextTemp("endif");

        var cond = exprVisitor.visit(condExpr);
        var code = new StringBuilder();

        code.append(cond.getComputation());
        code.append("if (").append(cond.getCode()).append(") goto ").append(thenLabel).append(END_STMT);
        code.append("goto ").append(endifLabel).append(END_STMT);

        code.append(thenLabel).append(":\n");
        code.append(visit(thenStmt));

        code.append(endifLabel).append(":\n");
        return code.toString();
    }

    private String visitWhileStmt(JmmNode node, Void unused) {
        // children: condition(EXPR), body(STMT)
        var condExpr = node.getChild(0);
        var bodyStmt = node.getChild(1);

        var whileCondLabel = ollirTypes.nextTemp("whileCond");
        var whileBodyLabel = ollirTypes.nextTemp("whileBody");
        var whileEndLabel = ollirTypes.nextTemp("whileEnd");

        var code = new StringBuilder();
        code.append(whileCondLabel).append(":\n");

        var cond = exprVisitor.visit(condExpr);
        code.append(cond.getComputation());
        code.append("if (").append(cond.getCode()).append(") goto ").append(whileBodyLabel).append(END_STMT);
        code.append("goto ").append(whileEndLabel).append(END_STMT);

        code.append(whileBodyLabel).append(":\n");
        code.append(visit(bodyStmt));
        code.append("goto ").append(whileCondLabel).append(END_STMT);

        code.append(whileEndLabel).append(":\n");
        return code.toString();
    }

    private String visitDoWhileStmt(JmmNode node, Void unused) {
        // children: body(STMT), condition(EXPR)
        var bodyStmt = node.getChild(0);
        var condExpr = node.getChild(1);

        var loopLabel = ollirTypes.nextTemp("doWhile");
        var loopEndLabel = ollirTypes.nextTemp("doWhileEnd");

        var code = new StringBuilder();
        code.append(loopLabel).append(":\n");
        code.append(visit(bodyStmt));

        var cond = exprVisitor.visit(condExpr);
        code.append(cond.getComputation());
        code.append("if (").append(cond.getCode()).append(") goto ").append(loopLabel).append(END_STMT);
        code.append(loopEndLabel).append(":\n");
        return code.toString();
    }

    private String visitForStmt(JmmNode node, Void unused) {
        var initNode = node.getObject(JmmAttributes.FOR_STMT.INIT_NODE, JmmNode.class);
        var condNode = node.getObject(JmmAttributes.FOR_STMT.CONDITION_NODE, JmmNode.class);
        var updateNode = node.getObject(JmmAttributes.FOR_STMT.UPDATE_NODE, JmmNode.class);
        var bodyStmt = node.getChild(node.getNumChildren() - 1);

        var forCondLabel = ollirTypes.nextTemp("forCond");
        var forBodyLabel = ollirTypes.nextTemp("forBody");
        var forEndLabel = ollirTypes.nextTemp("forEnd");

        var code = new StringBuilder();

        // init
        if (PRESENT_FOR_INIT.check(initNode)) {
            for (var assign : initNode.getChildren()) {
                if (FOR_ASSIGN.check(assign)) {
                    code.append(visitForAssign(assign));
                } else {
                    code.append(visit(assign));
                }
            }
        }

        code.append(forCondLabel).append(":\n");

        // condition
        if (PRESENT_FOR_CONDITION.check(condNode)) {
            var cond = exprVisitor.visit(condNode.getChild(0));
            code.append(cond.getComputation());
            code.append("if (").append(cond.getCode()).append(") goto ").append(forBodyLabel).append(END_STMT);
            code.append("goto ").append(forEndLabel).append(END_STMT);
        }

        code.append(forBodyLabel).append(":\n");
        code.append(visit(bodyStmt));

        // update
        if (PRESENT_FOR_UPDATE.check(updateNode)) {
            for (var assign : updateNode.getChildren()) {
                if (FOR_ASSIGN.check(assign)) {
                    code.append(visitForAssign(assign));
                } else {
                    code.append(visit(assign));
                }
            }
        }

        code.append("goto ").append(forCondLabel).append(END_STMT);
        code.append(forEndLabel).append(":\n");
        return code.toString();
    }

    private String visitForAssign(JmmNode node) {
        var varName = node.get(JmmAttributes.FOR_ASSIGN.NAME);
        var valueNode = node.getObject(JmmAttributes.FOR_ASSIGN.VALUE, JmmNode.class);

        JmmType varType = resolveVarType(varName);
        String typeStr = ollirTypes.toOllirType(varType);

        var val = exprVisitor.visit(valueNode);
        var code = new StringBuilder();
        code.append(val.getComputation());
        code.append(ollirTypes.sanitizeId(varName)).append(typeStr)
                .append(SPACE).append(ASSIGN).append(typeStr).append(SPACE)
                .append(val.getCode()).append(END_STMT);
        return code.toString();
    }

    // ---- Helpers ----

    private JmmType resolveVarType(String varName) {
        if (currentMethod != null) {
            var local = currentMethod.getLocalVariable(varName);
            if (local.isPresent()) return local.get().type();
            var param = currentMethod.getParameter(varName);
            if (param.isPresent()) return param.get().type();
        }
        var field = table.getField(varName);
        if (field.isPresent()) return field.get().type();
        return TypeUtils.intType();
    }

    private boolean isFieldVar(String varName) {
        if (currentMethod != null) {
            if (currentMethod.getLocalVariable(varName).isPresent()) return false;
            if (currentMethod.getParameter(varName).isPresent()) return false;
        }
        return table.getField(varName).isPresent();
    }

    private JmmNode makeThisNode(JmmNode context) {
        return new pt.up.fe.comp.jmm.ast.JmmNodeImpl(THIS_EXPR);
    }

    private JmmType getIndexedArrayType(JmmType arrayType) {
        if (!arrayType.isArray()) {
            return arrayType;
        }

        var jmmArrayType = arrayType.asArray();
        if (jmmArrayType.dimension() == 1) {
            return jmmArrayType.itemType();
        }

        return pt.up.fe.comp.jmm.analysis.table.type.impls.JmmArrayType.of(
                jmmArrayType.itemType(),
                jmmArrayType.dimension() - 1);
    }

    private String defaultVisit(JmmNode node, Void unused) {
        var code = new StringBuilder();
        for (var child : node.getChildren()) {
            var childCode = visit(child);
            if (childCode != null) code.append(childCode);
        }
        return code.toString();
    }
}
