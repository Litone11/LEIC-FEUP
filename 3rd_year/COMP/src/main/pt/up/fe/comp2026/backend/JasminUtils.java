package pt.up.fe.comp2026.backend;

import org.specs.comp.ollir.*;
import org.specs.comp.ollir.type.*;
import pt.up.fe.comp.jmm.analysis.table.reflection.Importer;
import pt.up.fe.comp.jmm.ollir.OllirResult;

import java.util.HashMap;
import java.util.Map;
import java.util.Set;

public class JasminUtils {

    private static final Set<String> JASMIN_KEYWORDS = Set.of(
            "field", "method", "class", "super", "interface", "implements", "throws",
            "signature", "default", "bridge", "synthetic", "enum", "var_args", "package",
            "inner", "outer", "static", "final", "abstract", "volatile", "transient",
            "native", "strictfp", "private", "public", "protected", "synchronized",
            "from", "to", "using", "is", "var", "code", "line", "try", "catch", "end",
            "extends", "limit", "stack", "locals"
    );

    public static String sanitizeJasminName(String name) {
        if (name == null || name.isEmpty()) return name;
        if (JASMIN_KEYWORDS.contains(name)) return "'" + name + "'";
        if (name.startsWith("$")) return "'" + name + "'";
        return name;
    }

    private final OllirResult ollirResult;

    private final Map<String, String> fullClassnames;

    private final Importer importer;

    public JasminUtils(OllirResult ollirResult) {
        this.ollirResult = ollirResult;
        this.importer = Importer.fromThisClassPath();
        fullClassnames = new HashMap<>();

        var thisClass = ollirResult.getOllirClass();
        var thisFqn = thisClass.getClassFullyQualifiedName().replace('.', '/');
        fullClassnames.put("this", thisFqn);
        fullClassnames.put(thisClass.getClassName(), thisFqn);
        fullClassnames.put("String", "java/lang/String");
        fullClassnames.put("Object", "java/lang/Object");

        for (var fullImport : ollirResult.getOllirClass().getImports()) {
            var splitted = fullImport.split("\\.");
            var key = splitted[splitted.length - 1];
            fullClassnames.put(key, fullImport.replace('.', '/'));
        }
    }

    public String getTypePrefix(Type type) {
        if (type instanceof BuiltinType builtinType) {
            return switch (builtinType.getKind()) {
                case INT32, BOOLEAN -> "i";
                case STRING -> "a";
                case VOID -> "";
            };
        }
        if (type instanceof ArrayType || type instanceof ClassType) {
            return "a";
        }
        throw new RuntimeException("Unknown type prefix for type '" + type + "'");
    }

    public String getTypeDescriptor(Type type) {
        if (type instanceof BuiltinType builtinType) {
            return switch (builtinType.getKind()) {
                case INT32 -> "I";
                case BOOLEAN -> "Z";
                case VOID -> "V";
                case STRING -> "Ljava/lang/String;";
            };
        }
        if (type instanceof ArrayType arrayType) {
            int dims = Math.max(1, arrayType.getNumDimensions());
            return "[".repeat(dims) + getTypeDescriptor(arrayType.getElementType());
        }
        if (type instanceof ClassType classType) {
            return "L" + resolveClassName(classType.getName()) + ";";
        }
        throw new RuntimeException("Not implemented for element type '" + type + "'");
    }

    public String resolveClassName(String name) {
        if (name == null || name.isEmpty()) {
            return "java/lang/Object";
        }
        var resolved = fullClassnames.get(name);
        if (resolved != null) {
            return resolved;
        }
        if (name.contains(".") || name.contains("/")) {
            return name.replace('.', '/');
        }
        var implicit = importer.loadImplicit(name);
        if (implicit.isPresent()) {
            var fqn = implicit.get().getName().replace('.', '/');
            fullClassnames.put(name, fqn);
            return fqn;
        }
        return name;
    }

    public String getModifier(AccessModifier accessModifier) {
        if (accessModifier == AccessModifier.DEFAULT) {
            return "public ";
        }
        return accessModifier.name().toLowerCase() + " ";
    }

    public String getMethodModifier(AccessModifier accessModifier) {
        if (accessModifier == AccessModifier.DEFAULT) {
            return "";
        }
        return accessModifier.name().toLowerCase() + " ";
    }

    public String getLoad(Descriptor reg) {
        var prefix = getTypePrefix(reg.getVarType());
        var value = reg.getVirtualReg();
        return prefix + "load" + regSuffix(value);
    }

    public String getStore(Descriptor reg) {
        var prefix = getTypePrefix(reg.getVarType());
        var value = reg.getVirtualReg();
        return prefix + "store" + regSuffix(value);
    }

    private String regSuffix(int reg) {
        if (reg >= 0 && reg <= 3) {
            return "_" + reg;
        }
        return " " + reg;
    }

    public String pushInt(int value) {
        if (value == -1) return "iconst_m1";
        if (value >= 0 && value <= 5) return "iconst_" + value;
        if (value >= Byte.MIN_VALUE && value <= Byte.MAX_VALUE) return "bipush " + value;
        if (value >= Short.MIN_VALUE && value <= Short.MAX_VALUE) return "sipush " + value;
        return "ldc " + value;
    }
}
