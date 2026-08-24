package pt.up.fe.comp2026.symboltable;

import pt.up.fe.comp.jmm.analysis.table.MethodSymbol;
import pt.up.fe.comp.jmm.analysis.table.Signature;
import pt.up.fe.comp.jmm.analysis.table.Symbol;
import pt.up.fe.comp.jmm.analysis.table.Visibility;
import pt.up.fe.comp.jmm.analysis.table.reflection.Importer;
import pt.up.fe.comp.jmm.analysis.table.type.impls.JmmPrimitiveType;
import pt.up.fe.comp.jmm.analysis.table.type.JmmType;
import pt.up.fe.comp.jmm.analysis.table.type.impls.JmmArrayType;
import pt.up.fe.comp.jmm.analysis.table.type.impls.JmmClassType;
import pt.up.fe.comp.jmm.ast.JmmNode;
import pt.up.fe.comp.jmm.report.Report;
import pt.up.fe.comp.jmm.report.Stage;
import pt.up.fe.comp2026.ast.NodeUtils;
import pt.up.fe.specs.util.SpecsCheck;

import java.util.*;

import static pt.up.fe.comp2026.jmm.ast.JmmKind.*;

public class JmmSymbolTableBuilder {

    private final JmmNode root;
    private final Importer importer;
    public String className;
    private final List<Report> reports;
    private final List<String> imports;

    /**
     * Only build() can create new instances, this ensures that each instance is used only once,
     * and we do not have to worry about "cleaning state".
     */
    private JmmSymbolTableBuilder(JmmNode root) {
        this.root = root;
        reports = new ArrayList<>();
        imports = new ArrayList<>();
        this.importer = Importer.fromThisClassPath();
    }

    private static Report newError(JmmNode node, String message) {
        return Report.newError(
                Stage.SEMANTIC,
                NodeUtils.getLine(node),
                NodeUtils.getColumn(node),
                message,
                null);
    }

    public static SymbolTableBuilderResult build(JmmNode root) {
        return new JmmSymbolTableBuilder(root).buildInternal();
    }

    private SymbolTableBuilderResult buildInternal() {
        var packageDecl = root.getChildren(PACKAGE_DECL).getFirst();
        var packagePathList = packageDecl.getObjectAsList("path", String.class);
        var packagePath = String.join(".", packagePathList);
        buildImports();

        var classDecl = root.getObject("classNode", JmmNode.class);
        SpecsCheck.checkArgument(CLASS_DECL.check(classDecl), () -> "Expected a class declaration: " + classDecl);

        this.className = classDecl.get("name");
        var fullyQualifiedName = packagePath + "." + className;
        var superQualifiedName = buildSuperQualifiedName(classDecl);
        var fields = buildFields(classDecl, fullyQualifiedName);
        var methods = buildMethods(classDecl, fullyQualifiedName);

        var symbolTable = new JmmSymbolTable(imports, fullyQualifiedName, superQualifiedName, fields, methods, importer);

        return new SymbolTableBuilderResult(symbolTable, reports);
    }

    private void buildImports() {
        for (var importDecl : root.getChildren(IMPORT_DECL)) {
            var path = String.join(".", importDecl.getObjectAsList("path", String.class));
            if (!imports.contains(path)) {
                imports.add(path);
            }

            if (!importer.inClassPath(path)) {
                reports.add(newError(importDecl, "Imported class '" + path + "' does not exist"));
            }
        }
    }

    private String buildSuperQualifiedName(JmmNode classDecl) {
        var superName = classDecl.getOptional("superName");

        if (superName.isEmpty()) {
            return null;
        }

        var superSimpleName = superName.get();

        if (superSimpleName.equals(className)) {
            reports.add(newError(classDecl, "Class '" + className + "' cannot extend itself"));
            return null;
        }

        var importedSuper = getImportedFullyQualifiedName(superSimpleName);
        if (importedSuper.isPresent()) {
            return importedSuper.get();
        }

        var implicitSuper = importer.loadImplicit(superSimpleName);
        if (implicitSuper.isPresent()) {
            return implicitSuper.get().getName();
        }

        reports.add(newError(classDecl, "Superclass '" + superSimpleName + "' is not imported"));
        return null;
    }

    private List<Symbol> buildFields(JmmNode classDecl, String currentClassFqn) {
        var fields = new ArrayList<Symbol>();
        var fieldNames = new HashSet<String>();

        for (var fieldDecl : classDecl.getChildren(VAR_DECL)) {
            var field = buildSymbol(fieldDecl, currentClassFqn);

            if (!fieldNames.add(field.name())) {
                reports.add(newError(fieldDecl, "Field '" + field.name() + "' is already defined"));
                continue;
            }

            fields.add(field);
        }
        return fields;
    }

    private List<MethodSymbol> buildMethods(JmmNode classDecl, String currentClassFqn) {
        var methods = new ArrayList<MethodSymbol>();
        var signatures = new HashSet<Signature>();

        for (var methodDecl : classDecl.getChildren(METHOD_DECL)) {
            var method = buildMethod(methodDecl, currentClassFqn);
            var signatureKey = method.signature();

            if (!signatures.add(signatureKey)) {
                reports.add(newError(methodDecl, "Method '" + method.name() + "' with the same signature is already defined"));
                continue;
            }

            methods.add(method);
        }

        return methods;
    }

    private MethodSymbol buildMethod(JmmNode method, String currentClassFqn) {
        var methodName = method.get("name");
        var returnTypeNode = method.getObject("returnType", JmmNode.class);
        var returnType = buildType(returnTypeNode, currentClassFqn);

        var params = new ArrayList<Symbol>();
        var paramNames = new HashSet<String>();
        for (var paramDecl : method.getDescendants(PARAM)) {
            var param = buildSymbol(paramDecl, currentClassFqn);
            if (!paramNames.add(param.name())) {
                reports.add(newError(paramDecl, "Parameter '" + param.name() + "' is already defined in method '" + methodName + "'"));
                continue;
            }
            params.add(param);
        }

        var locals = new ArrayList<Symbol>();
        var localNames = new HashSet<String>();
        for (var varDecl : method.getChildren(VAR_DECL)) {
            var local = buildSymbol(varDecl, currentClassFqn);
            if (paramNames.contains(local.name())) {
                reports.add(newError(varDecl, "Local variable '" + local.name() + "' conflicts with a parameter in method '" + methodName + "'"));
                continue;
            }
            if (!localNames.add(local.name())) {
                reports.add(newError(varDecl, "Local variable '" + local.name() + "' is already defined in method '" + methodName + "'"));
                continue;
            }
            locals.add(local);
        }

        var visibility = switch (method.getOptional("visibility").orElse("package-protected")) {
            case "public" -> Visibility.PUBLIC;
            case "private" -> Visibility.PRIVATE;
            case "protected" -> Visibility.PROTECTED;
            default -> Visibility.PACKAGE_PROTECTED;
        };
        var isStatic = method.getBoolean("isStatic", false);
        return new MethodSymbol(methodName, returnType, params, locals, isStatic, visibility);
    }

    private Symbol buildSymbol(JmmNode declNode, String currentClassFqn) {
        var typeNode = declNode.getObject("typeNode", JmmNode.class);
        return new Symbol(buildType(typeNode, currentClassFqn), declNode.get("name"));
    }

    private JmmType buildType(JmmNode typeNode, String currentClassFqn) {
        var typeName = typeNode.get("name");
        var arrayDims = typeNode.getInteger("arrayDims", 0);

        JmmType baseType = JmmPrimitiveType.fromString(typeName)
                .<JmmType>map(type -> type)
                .orElseGet(() -> buildClassType(typeName, currentClassFqn));

        if (arrayDims > 0) {
            return JmmArrayType.of(baseType, arrayDims);
        }

        return baseType;
    }

    private JmmClassType buildClassType(String typeName, String currentClassFqn) {
        if (typeName.equals(className)) {
            return JmmClassType.ofInstance(currentClassFqn, false);
        }

        var importedType = getImportedFullyQualifiedName(typeName);
        if (importedType.isPresent()) {
            return JmmClassType.ofInstance(importedType.get(), true);
        }

        var implicitType = importer.loadImplicit(typeName);
        if (implicitType.isPresent()) {
            return JmmClassType.ofInstance(implicitType.get().getName(), true);
        }

        return JmmClassType.ofInstance(typeName, false);
    }

    private Optional<String> getImportedFullyQualifiedName(String simpleName) {
        var dotName = "." + simpleName;
        return imports.stream()
                .filter(importName -> importName.equals(simpleName) || importName.endsWith(dotName))
                .findFirst();
    }

}