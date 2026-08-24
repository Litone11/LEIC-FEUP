package pt.up.fe.comp2026.backend;

import org.specs.comp.ollir.*;
import org.specs.comp.ollir.inst.*;
import org.specs.comp.ollir.tree.TreeNode;
import org.specs.comp.ollir.type.ArrayType;
import org.specs.comp.ollir.type.BuiltinKind;
import org.specs.comp.ollir.type.BuiltinType;
import org.specs.comp.ollir.type.ClassType;
import pt.up.fe.comp.jmm.ollir.OllirResult;
import pt.up.fe.comp.jmm.report.Report;
import pt.up.fe.specs.util.classmap.FunctionClassMap;
import pt.up.fe.specs.util.exceptions.NotImplementedException;
import pt.up.fe.specs.util.utilities.StringLines;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import java.util.stream.Collectors;

/**
 * Generates Jasmin code from an OllirResult.
 * <p>
 * One JasminGenerator instance per OllirResult.
 */
public class JasminGenerator {

    private static final String NL = "\n";
    private static final String TAB = "   ";

    private final OllirResult ollirResult;

    private final List<Report> reports;

    private String code;

    private Method currentMethod;

    private boolean isInsideAssignment;

    private final JasminUtils types;
    private final FunctionClassMap<TreeNode, String> generators;

    private int currentStack;
    private int maxStack;
    private int labelCounter;

    public JasminGenerator(OllirResult ollirResult) {
        this.ollirResult = ollirResult;
        this.reports = new ArrayList<>();
        this.code = null;
        this.currentMethod = null;
        this.isInsideAssignment = false;
        this.types = new JasminUtils(ollirResult);
        this.generators = new FunctionClassMap<>();
        generators .put(ClassUnit.class, this::generateClassUnit);
        generators.put(Method.class, this::generateMethod);
        generators.put(AssignInstruction.class, this::generateAssign);
        generators.put(SingleOpInstruction.class, this::generateSingleOp);
        generators.put(LiteralElement.class, this::generateLiteral);
        generators.put(ArrayOperand.class, this::generateArrayAccess);
        generators.put(Operand.class, this::generateOperand);
        generators.put(BinaryOpInstruction.class, this::generateBinaryOp);
        generators.put(UnaryOpInstruction.class, this::generateUnaryOp);
        generators.put(ReturnInstruction.class, this::generateReturn);
        generators.put(CallInstruction.class, this::generateCall);
        generators.put(GetFieldInstruction.class, this::generateGetField);
        generators.put(PutFieldInstruction.class, this::generatePutField);
        generators.put(CondBranchInstruction.class, this::generateCondBranch);
        generators.put(GotoInstruction.class, this::generateGoto);
    }

     private String apply(TreeNode node) {
        return generators.apply(node);
    }

    public List<Report> getReports() {
        return reports;
    }

    public String build() {
        if (code == null) {
            code = apply(ollirResult.getOllirClass());
            try {
                java.nio.file.Files.writeString(java.nio.file.Path.of("/tmp/last-jasmin.j"), code);
            } catch (Exception ignored) {}
        }
        return code;
    }

        private void pushStack(int delta) {
        currentStack += delta;
        if (currentStack > maxStack) maxStack = currentStack;
        if (currentStack < 0) currentStack = 0;
    }
    

    private String generateClassUnit(ClassUnit classUnit) {
        var code = new StringBuilder();

        var nameWithPackage = classUnit.getClassFullyQualifiedName().replace('.', '/');
        code.append(".class public ").append(nameWithPackage).append(NL);

        var superClass = classUnit.getSuperClass();
        var fullSuperClass = (superClass == null || superClass.isEmpty() || superClass.equals("Object"))
                ? "java/lang/Object"
                : types.resolveClassName(superClass);

        code.append(".super ").append(fullSuperClass).append(NL).append(NL);

        for (var field : classUnit.getFields()) {
            code.append(generateField(field)).append(NL);
        }
        if (!classUnit.getFields().isEmpty()) code.append(NL);

        Method constructMethod = null;
        for (var method : classUnit.getMethods()) {
            if (method.isConstructMethod()) {
                constructMethod = method;
                break;
            }
        }
        if (constructMethod != null) {
            code.append(apply(constructMethod));
        } else {
            var defaultConstructor = """
                    ; default constructor
                    .method public <init>()V
                        aload_0
                        invokespecial %s/<init>()V
                        return
                    .end method
                    """.formatted(fullSuperClass);
            code.append(defaultConstructor);
        }

        for (var method : classUnit.getMethods()) {
            if (method.isConstructMethod()) continue;
            code.append(apply(method));
        }

        return code.toString();
    }

    private String generateField(Field field) {
        var modifier = types.getModifier(field.getFieldAccessModifier());
        var staticPart = field.isStaticField() ? "static " : "";
        var finalPart = field.isFinalField() ? "final " : "";
        var descriptor = types.getTypeDescriptor(field.getFieldType());
        var name = JasminUtils.sanitizeJasminName(field.getFieldName());
        var base = ".field " + modifier + staticPart + finalPart + name + " " + descriptor;
        if (field.isInitialized()) {
            base += " = " + field.getInitialValue();
        }
        return base;
    }

    private String generateMethod(Method method) {
        currentMethod = method;
        currentStack = 0;
        maxStack = 0;
        labelCounter = 0;

        var code = new StringBuilder();

        var isConstruct = method.isConstructMethod();
        var modifier = isConstruct ? "public " : types.getMethodModifier(method.getMethodAccessModifier());
        var staticMod = method.isStaticMethod() ? "static " : "";
        var methodName = isConstruct ? "<init>" : method.getMethodName();

        var params = method.getParams().stream()
                .map(p -> types.getTypeDescriptor(p.getType()))
                .collect(Collectors.joining());

        var returnType = types.getTypeDescriptor(method.getReturnType());

        code.append("\n.method ").append(modifier).append(staticMod)
                .append(methodName).append("(").append(params).append(")").append(returnType).append(NL);

        var bodyCode = new StringBuilder();
        for (var inst : method.getInstructions()) {
            var labels = method.getLabels(inst);
            if (labels != null) {
                for (var label : labels) {
                    bodyCode.append(label).append(":").append(NL);
                }
            }
            var instCode = StringLines.getLines(apply(inst)).stream()
                    .collect(Collectors.joining(NL + TAB, TAB, NL));
            bodyCode.append(instCode);
        }

        var instructions = method.getInstructions();
        boolean endsWithReturn = !instructions.isEmpty()
                && instructions.get(instructions.size() - 1) instanceof ReturnInstruction;
        if (!endsWithReturn
                && method.getReturnType() instanceof BuiltinType bt
                && bt.getKind() == BuiltinKind.VOID) {
            bodyCode.append(TAB).append("return").append(NL);
        }

        var locals = computeLocalsLimit(method);
        var stack = Math.max(maxStack, 1);

        code.append(TAB).append(".limit stack ").append(stack).append(NL);
        code.append(TAB).append(".limit locals ").append(locals).append(NL);
        code.append(bodyCode);
        code.append(".end method").append(NL);

        currentMethod = null;
        return code.toString();
    }

    private int computeLocalsLimit(Method method) {
        int max = method.isStaticMethod() ? 0 : 1;
        for (var desc : method.getVarTable().values()) {
            max = Math.max(max, desc.getVirtualReg() + 1);
        }
        max = Math.max(max, method.getParams().size() + (method.isStaticMethod() ? 0 : 1));
        return Math.max(max, 1);
    }

    private String generateAssign(AssignInstruction assign) {
        try {
            isInsideAssignment = true;

            var lhs = assign.getDest();

            if (lhs instanceof ArrayOperand arrayOp) {
                var sb = new StringBuilder();
                var reg = currentMethod.getVarTable().get(arrayOp.getName());
                sb.append("aload").append(regSuffix(reg.getVirtualReg())).append(NL);
                pushStack(+1);
                for (var index : arrayOp.getIndexOperands()) {
                    sb.append(apply(index));
                }
                sb.append(apply(assign.getRhs()));
                var arrayType = (ArrayType) reg.getVarType();
                sb.append(isArrayElementReference(arrayType) ? "aastore" : "iastore").append(NL);
                pushStack(-3);
                return sb.toString();
            }

            var operand = (Operand) lhs;
            var reg = currentMethod.getVarTable().get(operand.getName());

            var iincCode = tryIinc(assign, reg);
            if (iincCode != null) return iincCode;

            var sb = new StringBuilder();
            sb.append(apply(assign.getRhs()));
            sb.append(types.getStore(reg)).append(NL);
            pushStack(-1);
            return sb.toString();
        } finally {
            isInsideAssignment = false;
        }
    }

    private String generateSingleOp(SingleOpInstruction singleOp) {
        return apply(singleOp.getSingleOperand());
    }

    private String generateLiteral(LiteralElement literal) {
        var type = literal.getType();
        if (type instanceof BuiltinType bt
                && (bt.getKind() == BuiltinKind.INT32 || bt.getKind() == BuiltinKind.BOOLEAN)) {
            try {
                int value = Integer.parseInt(literal.getLiteral());
                pushStack(+1);
                return types.pushInt(value) + NL;
            } catch (NumberFormatException ignored) {}
        }
        pushStack(+1);
        return "ldc " + literal.getLiteral() + NL;
    }

    private String generateOperand(Operand operand) {
        var reg = currentMethod.getVarTable().get(operand.getName());
        if (reg == null) {
            return "";
        }
        pushStack(+1);
        return types.getLoad(reg) + NL;
    }

    private String regSuffix(int reg) {
        return (reg >= 0 && reg <= 3) ? "_" + reg : " " + reg;
    }

    private String generateReturn(ReturnInstruction returnInst) {
        var sb = new StringBuilder();
        var returnType = returnInst.getReturnType();
        var prefix = types.getTypePrefix(returnType);

        returnInst.getOperand().ifPresent(op -> sb.append(apply(op)));

        sb.append(prefix).append("return").append(NL);
        if (returnType instanceof BuiltinType bt && bt.getKind() == BuiltinKind.VOID) {
        } else {
            pushStack(-1);
        }
        return sb.toString();
    }

    private String tryIinc(AssignInstruction assign, Descriptor reg) {
        if (reg == null) return null;
        if (!(reg.getVarType() instanceof BuiltinType bt) || bt.getKind() != BuiltinKind.INT32) return null;
        var rhs = assign.getRhs();
        if (!(rhs instanceof BinaryOpInstruction bin)) return null;
        var opType = bin.getOperation().getOpType();
        if (opType != OperationType.ADD && opType != OperationType.SUB) return null;

        var dest = (Operand) assign.getDest();
        var left = bin.getLeftOperand();
        var right = bin.getRightOperand();

        Integer delta = null;
        if (left instanceof Operand leftOp && !(left instanceof ArrayOperand)
                && leftOp.getName().equals(dest.getName())
                && right instanceof LiteralElement litR) {
            try {
                int v = Integer.parseInt(litR.getLiteral());
                delta = (opType == OperationType.ADD) ? v : -v;
            } catch (NumberFormatException ignored) {}
        }
        if (delta == null && opType == OperationType.ADD
                && right instanceof Operand rightOp && !(right instanceof ArrayOperand)
                && rightOp.getName().equals(dest.getName())
                && left instanceof LiteralElement litL) {
            try {
                delta = Integer.parseInt(litL.getLiteral());
            } catch (NumberFormatException ignored) {}
        }
        if (delta == null) return null;
        if (delta < Short.MIN_VALUE || delta > Short.MAX_VALUE) return null;
        return "iinc " + reg.getVirtualReg() + " " + delta + NL;
    }

    private String generateBinaryOp(BinaryOpInstruction binaryOp) {
        var sb = new StringBuilder();
        var opType = binaryOp.getOperation().getOpType();

        switch (opType) {
            case ADD, SUB, MUL, DIV, REM,
                 BITWISE_AND, BITWISE_OR, XOR, SHL, SHR, SHRR -> {
                sb.append(apply(binaryOp.getLeftOperand()));
                sb.append(apply(binaryOp.getRightOperand()));
                sb.append(arithmeticOpcode(opType)).append(NL);
                pushStack(-1);
            }
            case LTH, GTH, LTE, GTE, EQ, NEQ -> {
                sb.append(generateBooleanFromCompare(binaryOp, opType));
            }
            case LOGICAL_AND -> {
                int n = labelCounter++;
                var labelFalse = "andFalse_" + n;
                var labelEnd = "andEnd_" + n;
                sb.append(apply(binaryOp.getLeftOperand()));
                sb.append("ifeq ").append(labelFalse).append(NL);
                pushStack(-1);
                sb.append(apply(binaryOp.getRightOperand()));
                sb.append("goto ").append(labelEnd).append(NL);
                sb.append(labelFalse).append(":").append(NL);
                sb.append("iconst_0").append(NL);
                pushStack(+1);
                sb.append(labelEnd).append(":").append(NL);
            }
            case LOGICAL_OR -> {
                int n = labelCounter++;
                var labelTrue = "orTrue_" + n;
                var labelEnd = "orEnd_" + n;
                sb.append(apply(binaryOp.getLeftOperand()));
                sb.append("ifne ").append(labelTrue).append(NL);
                pushStack(-1);
                sb.append(apply(binaryOp.getRightOperand()));
                sb.append("goto ").append(labelEnd).append(NL);
                sb.append(labelTrue).append(":").append(NL);
                sb.append("iconst_1").append(NL);
                pushStack(+1);
                sb.append(labelEnd).append(":").append(NL);
            }
            default -> throw new NotImplementedException("BinaryOp: " + opType);
        }

        return sb.toString();
    }

    private String arithmeticOpcode(OperationType op) {
        return switch (op) {
            case ADD -> "iadd";
            case SUB -> "isub";
            case MUL -> "imul";
            case DIV -> "idiv";
            case REM -> "irem";
            case BITWISE_AND -> "iand";
            case BITWISE_OR -> "ior";
            case XOR -> "ixor";
            case SHL -> "ishl";
            case SHR -> "ishr";
            case SHRR -> "iushr";
            default -> throw new NotImplementedException("arithmetic opcode: " + op);
        };
    }

    private String generateBooleanFromCompare(BinaryOpInstruction bin, OperationType op) {
        var sb = new StringBuilder();
        int n = labelCounter++;
        var labelTrue = "cmpTrue_" + n;
        var labelEnd = "cmpEnd_" + n;

        var left = bin.getLeftOperand();
        var right = bin.getRightOperand();

        var zeroBranch = zeroComparisonBranch(left, right, op, labelTrue);
        if (zeroBranch != null) {
            sb.append(zeroBranch.code);
            sb.append("iconst_0").append(NL);
            pushStack(+1);
            sb.append("goto ").append(labelEnd).append(NL);
            sb.append(labelTrue).append(":").append(NL);
            pushStack(-1);
            sb.append("iconst_1").append(NL);
            pushStack(+1);
            sb.append(labelEnd).append(":").append(NL);
            return sb.toString();
        }

        sb.append(apply(left));
        sb.append(apply(right));
        sb.append(icmpOpcode(op)).append(" ").append(labelTrue).append(NL);
        pushStack(-2);
        sb.append("iconst_0").append(NL);
        pushStack(+1);
        sb.append("goto ").append(labelEnd).append(NL);
        sb.append(labelTrue).append(":").append(NL);
        pushStack(-1);
        sb.append("iconst_1").append(NL);
        pushStack(+1);
        sb.append(labelEnd).append(":").append(NL);
        return sb.toString();
    }

    private record ZeroBranch(String code) {}

    private ZeroBranch zeroComparisonBranch(Element left, Element right, OperationType op, String label) {
        Element other = null;
        boolean leftIsZero = isLiteralZero(left);
        boolean rightIsZero = isLiteralZero(right);
        String opcode;
        if (rightIsZero) {
            other = left;
            opcode = switch (op) {
                case LTH -> "iflt";
                case GTH -> "ifgt";
                case LTE -> "ifle";
                case GTE -> "ifge";
                case EQ  -> "ifeq";
                case NEQ -> "ifne";
                default -> null;
            };
        } else if (leftIsZero) {
            other = right;
            opcode = switch (op) {
                case LTH -> "ifgt";
                case GTH -> "iflt";
                case LTE -> "ifge";
                case GTE -> "ifle";
                case EQ  -> "ifeq";
                case NEQ -> "ifne";
                default -> null;
            };
        } else {
            return null;
        }
        if (opcode == null) return null;
        var sb = new StringBuilder();
        sb.append(apply(other));
        sb.append(opcode).append(" ").append(label).append(NL);
        pushStack(-1);
        return new ZeroBranch(sb.toString());
    }

    private boolean isLiteralZero(Element e) {
        if (!(e instanceof LiteralElement lit)) return false;
        try {
            return Integer.parseInt(lit.getLiteral()) == 0;
        } catch (NumberFormatException ex) {
            return false;
        }
    }

    private String icmpOpcode(OperationType op) {
        return switch (op) {
            case LTH -> "if_icmplt";
            case GTH -> "if_icmpgt";
            case LTE -> "if_icmple";
            case GTE -> "if_icmpge";
            case EQ  -> "if_icmpeq";
            case NEQ -> "if_icmpne";
            default -> throw new NotImplementedException("icmp: " + op);
        };
    }

    private String generateUnaryOp(UnaryOpInstruction unary) {
        var op = unary.getOperation().getOpType();
        var sb = new StringBuilder();
        sb.append(apply(unary.getOperand()));
        if (op == OperationType.LOGICAL_NOT || op == OperationType.BITWISE_NOT) {
            sb.append("iconst_1").append(NL);
            pushStack(+1);
            sb.append("ixor").append(NL);
            pushStack(-1);
            return sb.toString();
        }
        throw new NotImplementedException("unary: " + op);
    }

    private String generateCondBranch(CondBranchInstruction branch) {
        var sb = new StringBuilder();
        var label = branch.getLabel();

        if (branch instanceof SingleOpCondInstruction single) {
            var operand = single.getCondition().getSingleOperand();
            sb.append(apply(operand));
            sb.append("ifne ").append(label).append(NL);
            pushStack(-1);
            return sb.toString();
        }

        if (branch instanceof OpCondInstruction opCond) {
            var cond = opCond.getCondition();
            if (cond instanceof BinaryOpInstruction bin) {
                var opType = bin.getOperation().getOpType();
                if (isComparison(opType)) {
                    var zb = zeroComparisonBranch(bin.getLeftOperand(), bin.getRightOperand(), opType, label);
                    if (zb != null) {
                        sb.append(zb.code);
                        return sb.toString();
                    }
                    sb.append(apply(bin.getLeftOperand()));
                    sb.append(apply(bin.getRightOperand()));
                    sb.append(icmpOpcode(opType)).append(" ").append(label).append(NL);
                    pushStack(-2);
                    return sb.toString();
                }
                sb.append(apply(bin));
                sb.append("ifne ").append(label).append(NL);
                pushStack(-1);
                return sb.toString();
            }
            if (cond instanceof UnaryOpInstruction unary
                    && unary.getOperation().getOpType() == OperationType.LOGICAL_NOT) {
                sb.append(apply(unary.getOperand()));
                sb.append("ifeq ").append(label).append(NL);
                pushStack(-1);
                return sb.toString();
            }
            sb.append(apply(cond));
            sb.append("ifne ").append(label).append(NL);
            pushStack(-1);
            return sb.toString();
        }

        throw new NotImplementedException("CondBranch: " + branch.getClass().getSimpleName());
    }

    private boolean isComparison(OperationType op) {
        return op == OperationType.LTH || op == OperationType.GTH
                || op == OperationType.LTE || op == OperationType.GTE
                || op == OperationType.EQ  || op == OperationType.NEQ;
    }

    private String generateGoto(GotoInstruction goTo) {
        return "goto " + goTo.getLabel() + NL;
    }

    private String generateArrayAccess(ArrayOperand operand) {
        var sb = new StringBuilder();
        var reg = currentMethod.getVarTable().get(operand.getName());
        sb.append("aload").append(regSuffix(reg.getVirtualReg())).append(NL);
        pushStack(+1);
        for (var index : operand.getIndexOperands()) {
            sb.append(apply(index));
        }
        var arrayType = (ArrayType) reg.getVarType();
        boolean isRefElement = isArrayElementReference(arrayType);
        sb.append(isRefElement ? "aaload" : "iaload").append(NL);
        pushStack(-1);
        return sb.toString();
    }

    private boolean isReferenceType(org.specs.comp.ollir.type.Type type) {
        if (type instanceof BuiltinType bt) {
            return bt.getKind() != BuiltinKind.INT32 && bt.getKind() != BuiltinKind.BOOLEAN;
        }
        return true;
    }

    private boolean isArrayElementReference(ArrayType arrayType) {
        if (Math.max(1, arrayType.getNumDimensions()) > 1) {
            return true;
        }
        return isReferenceType(arrayType.getElementType());
    }

    private String generateCall(CallInstruction call) {
        if (call instanceof InvokeStaticInstruction) return generateInvokeStatic((InvokeStaticInstruction) call);
        if (call instanceof InvokeVirtualInstruction) return generateInvokeVirtual((InvokeVirtualInstruction) call);
        if (call instanceof InvokeSpecialInstruction) return generateInvokeSpecial((InvokeSpecialInstruction) call);
        if (call instanceof NewInstruction) return generateNew((NewInstruction) call);
        if (call instanceof ArrayLengthInstruction) return generateArrayLength((ArrayLengthInstruction) call);
        throw new NotImplementedException("Call: " + call.getClass().getSimpleName());
    }

    private String getMethodNameLiteral(CallInstruction call) {
        var nameEl = call.getMethodName();
        if (nameEl instanceof LiteralElement lit) {
            var s = lit.getLiteral();
            if (s.startsWith("\"") && s.endsWith("\"")) s = s.substring(1, s.length() - 1);
            return s;
        }
        if (nameEl instanceof Operand op) return op.getName();
        return nameEl.toString();
    }

    private String buildInvocationDescriptor(CallInstruction call) {
        var args = call.getArguments().stream()
                .map(a -> types.getTypeDescriptor(a.getType()))
                .collect(Collectors.joining());
        return "(" + args + ")" + types.getTypeDescriptor(call.getReturnType());
    }

    private int callStackDelta(CallInstruction call, boolean hasReceiver) {
        int delta = -call.getArguments().size();
        if (hasReceiver) delta -= 1;
        if (!(call.getReturnType() instanceof BuiltinType bt) || bt.getKind() != BuiltinKind.VOID) {
            delta += 1;
        }
        return delta;
    }

    private String generateInvokeStatic(InvokeStaticInstruction call) {
        var sb = new StringBuilder();
        var className = ((Operand) call.getCaller()).getName();
        var resolved = types.resolveClassName(className);
        for (var arg : call.getArguments()) sb.append(apply(arg));
        sb.append("invokestatic ").append(resolved).append("/").append(getMethodNameLiteral(call))
                .append(buildInvocationDescriptor(call)).append(NL);
        pushStack(callStackDelta(call, false));
        sb.append(maybePop(call));
        return sb.toString();
    }

    private String generateInvokeVirtual(InvokeVirtualInstruction call) {
        var sb = new StringBuilder();
        sb.append(apply(call.getCaller()));
        for (var arg : call.getArguments()) sb.append(apply(arg));
        var callerType = call.getCaller().getType();
        var className = (callerType instanceof ClassType ct) ? types.resolveClassName(ct.getName()) : "java/lang/Object";
        sb.append("invokevirtual ").append(className).append("/").append(getMethodNameLiteral(call))
                .append(buildInvocationDescriptor(call)).append(NL);
        pushStack(callStackDelta(call, true));
        sb.append(maybePop(call));
        return sb.toString();
    }

    private String generateInvokeSpecial(InvokeSpecialInstruction call) {
        var sb = new StringBuilder();
        sb.append(apply(call.getCaller()));
        for (var arg : call.getArguments()) sb.append(apply(arg));
        var callerType = call.getCaller().getType();
        String className;
        if (call.getSuperClass().isPresent()) {
            className = types.resolveClassName(call.getSuperClass().get());
        } else if (callerType instanceof ClassType ct) {
            className = types.resolveClassName(ct.getName());
        } else {
            className = "java/lang/Object";
        }
        sb.append("invokespecial ").append(className).append("/").append(getMethodNameLiteral(call))
                .append(buildInvocationDescriptor(call)).append(NL);
        pushStack(callStackDelta(call, true));
        sb.append(maybePop(call));
        return sb.toString();
    }

    private String maybePop(CallInstruction call) {
        if (isInsideAssignment) return "";
        if (call.getReturnType() instanceof BuiltinType bt && bt.getKind() == BuiltinKind.VOID) return "";
        pushStack(-1);
        return "pop" + NL;
    }

    private String generateNew(NewInstruction call) {
        var sb = new StringBuilder();
        var returnType = call.getReturnType();
        if (returnType instanceof ArrayType arr) {
            var dimArgs = call.getArguments().stream()
                    .filter(a -> !isClassNameOperand(a))
                    .toList();
            for (var arg : dimArgs) {
                sb.append(apply(arg));
            }
            var elementType = arr.getElementType();
            int dims = dimArgs.size();
            if (dims > 1) {
                var descriptor = types.getTypeDescriptor(returnType);
                sb.append("multianewarray ").append(descriptor).append(" ").append(dims).append(NL);
                pushStack(1 - dims);
            } else if (arr.getNumDimensions() > 1 || elementType instanceof ArrayType) {
                var descriptor = types.getTypeDescriptor(returnType);
                sb.append("anewarray ").append(descriptor.substring(1)).append(NL);
            } else if (elementType instanceof BuiltinType bt && bt.getKind() == BuiltinKind.INT32) {
                sb.append("newarray int").append(NL);
            } else if (elementType instanceof BuiltinType bt && bt.getKind() == BuiltinKind.BOOLEAN) {
                sb.append("newarray boolean").append(NL);
            } else if (elementType instanceof ClassType ct) {
                sb.append("anewarray ").append(types.resolveClassName(ct.getName())).append(NL);
            } else {
                sb.append("newarray int").append(NL);
            }
            return sb.toString();
        }
        String className;
        if (call.getCaller() instanceof Operand op) {
            className = types.resolveClassName(op.getName());
        } else {
            className = "java/lang/Object";
        }
        sb.append("new ").append(className).append(NL);
        pushStack(+1);
        return sb.toString();
    }

    private boolean isClassNameOperand(Element e) {
        if (!(e instanceof Operand op)) return false;
        return currentMethod != null && !currentMethod.getVarTable().containsKey(op.getName());
    }

    private String generateArrayLength(ArrayLengthInstruction call) {
        var sb = new StringBuilder();
        sb.append(apply(call.getCaller()));
        sb.append("arraylength").append(NL);
        return sb.toString();
    }

    private String generateGetField(GetFieldInstruction inst) {
        var sb = new StringBuilder();
        sb.append(apply(inst.getObject()));
        var ownerType = inst.getObject().getType();
        var owner = (ownerType instanceof ClassType ct) ? types.resolveClassName(ct.getName())
                : ollirResult.getOllirClass().getClassName();
        var fieldName = inst.getField().getName();
        var descriptor = types.getTypeDescriptor(inst.getField().getType());
        sb.append("getfield ").append(owner).append("/").append(fieldName).append(" ").append(descriptor).append(NL);
        return sb.toString();
    }

    private String generatePutField(PutFieldInstruction inst) {
        var sb = new StringBuilder();
        sb.append(apply(inst.getObject()));
        sb.append(apply(inst.getValue()));
        var ownerType = inst.getObject().getType();
        var owner = (ownerType instanceof ClassType ct) ? types.resolveClassName(ct.getName())
                : ollirResult.getOllirClass().getClassName();
        var fieldName = inst.getField().getName();
        var descriptor = types.getTypeDescriptor(inst.getField().getType());
        sb.append("putfield ").append(owner).append("/").append(fieldName).append(" ").append(descriptor).append(NL);
        pushStack(-2);
        return sb.toString();
    }
}
