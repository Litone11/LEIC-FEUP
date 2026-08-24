package pt.up.fe.comp2026.optimization;

import org.specs.comp.ollir.*;
import org.specs.comp.ollir.inst.*;

import java.util.*;

/**
 * Performs register allocation using liveness analysis and graph coloring.
 */
public class RegisterAllocator {

    /**
     * Allocates registers for all methods in the class.
     *
     * @param classUnit     the OLLIR class
     * @param maxRegisters  total register budget (including this and params). 0 = minimize, -1 = no constraint.
     */
    public static void allocate(ClassUnit classUnit, int maxRegisters) {
        for (var method : classUnit.getMethods()) {
            allocateMethod(method, maxRegisters);
        }
    }

    private static void allocateMethod(Method method, int maxRegisters) {
        if (maxRegisters == -1) return; // No constraint, keep as-is

        method.buildCFG();

        boolean isStatic = method.isStaticMethod();
        int numParams = method.getParams().size();
        int firstLocalReg = (isStatic ? 0 : 1) + numParams;

        var varTable = method.getVarTable();

        // Collect local variable names (not this, not params, not fields)
        var locals = new ArrayList<String>();
        for (var entry : varTable.entrySet()) {
            var name = entry.getKey();
            var desc = entry.getValue();
            if (desc.getScope() == VarScope.LOCAL && !name.equals("this")) {
                locals.add(name);
            }
        }

        if (locals.isEmpty()) return;

        // Compute liveness
        var instructions = method.getInstructions();
        var in = new HashMap<Instruction, Set<String>>();
        var out = new HashMap<Instruction, Set<String>>();
        for (var inst : instructions) {
            in.put(inst, new HashSet<>());
            out.put(inst, new HashSet<>());
        }

        computeLiveness(method, instructions, varTable, in, out);

        // Build interference graph
        var interferences = buildInterferenceGraph(instructions, varTable, out, locals);

        // Determine K
        int K;
        if (maxRegisters == 0) {
            K = computeMinColorsNeeded(interferences, locals);
        } else {
            K = maxRegisters - firstLocalReg;
            if (K < 0) throw new RuntimeException("Not enough registers: requested " + maxRegisters
                    + " but need at least " + firstLocalReg + " for this and params");
            if (K == 0) K = locals.size(); // fallback: each local gets its own reg
        }

        // Graph coloring
        var coloring = graphColor(interferences, locals, K);

        // Map colors to register numbers starting from firstLocalReg
        for (var local : locals) {
            int color = coloring.getOrDefault(local, 0);
            varTable.get(local).setVirtualReg(firstLocalReg + color);
        }
    }

    // ---- Liveness Analysis ----

    private static void computeLiveness(Method method,
                                        List<Instruction> instructions,
                                        HashMap<String, Descriptor> varTable,
                                        HashMap<Instruction, Set<String>> in,
                                        HashMap<Instruction, Set<String>> out) {
        boolean changed = true;
        while (changed) {
            changed = false;
            // Backward iteration
            for (int i = instructions.size() - 1; i >= 0; i--) {
                var inst = instructions.get(i);

                // out[i] = ∪ in[j] for j in successors
                var newOut = new HashSet<String>();
                for (var succ : inst.getSuccessorsAsInst()) {
                    newOut.addAll(in.get(succ));
                }

                // in[i] = use[i] ∪ (out[i] - def[i])
                var use = getUse(inst, varTable);
                var def = getDef(inst, varTable);

                var newIn = new HashSet<>(use);
                var outMinusDef = new HashSet<>(newOut);
                outMinusDef.removeAll(def);
                newIn.addAll(outMinusDef);

                if (!newOut.equals(out.get(inst)) || !newIn.equals(in.get(inst))) {
                    out.put(inst, newOut);
                    in.put(inst, newIn);
                    changed = true;
                }
            }
        }
    }

    private static Set<String> getDef(Instruction inst, HashMap<String, Descriptor> varTable) {
        if (inst.getInstType() == InstructionType.ASSIGN) {
            var assign = (AssignInstruction) inst;
            var dest = assign.getDest();
            if (!dest.isLiteral()) {
                var op = (Operand) dest;
                var name = op.getName();
                if (isLocalVar(name, varTable)) {
                    // For array assignment, the array itself is used (not defined)
                    if (!(op instanceof ArrayOperand)) {
                        return Set.of(name);
                    }
                }
            }
        }
        return Set.of();
    }

    private static Set<String> getUse(Instruction inst, HashMap<String, Descriptor> varTable) {
        var use = new HashSet<String>();
        collectUse(inst, varTable, use);
        return use;
    }

    private static void collectUse(Instruction inst, HashMap<String, Descriptor> varTable, Set<String> use) {
        switch (inst.getInstType()) {
            case ASSIGN -> {
                var assign = (AssignInstruction) inst;
                var dest = assign.getDest();
                // If dest is array operand, the array itself is used
                if (!dest.isLiteral() && dest instanceof ArrayOperand ao) {
                    addVarIfLocal(ao.getName(), varTable, use);
                    for (var idx : ao.getIndexOperands()) {
                        addElemIfLocal(idx, varTable, use);
                    }
                }
                collectUse(assign.getRhs(), varTable, use);
            }
            case BINARYOPER -> {
                var binOp = (BinaryOpInstruction) inst;
                addElemIfLocal(binOp.getLeftOperand(), varTable, use);
                addElemIfLocal(binOp.getRightOperand(), varTable, use);
            }
            case UNARYOPER -> {
                var unaryOp = (UnaryOpInstruction) inst;
                addElemIfLocal(unaryOp.getOperand(), varTable, use);
            }
            case NOPER -> {
                var singleOp = (SingleOpInstruction) inst;
                addElemIfLocal(singleOp.getSingleOperand(), varTable, use);
            }
            case CALL -> {
                var call = (CallInstruction) inst;
                if (!(call instanceof NewInstruction)) {
                    var operands = call.getOperands();
                    if (!operands.isEmpty()) {
                        addElemIfLocal(operands.get(0), varTable, use);
                    }
                    for (var op : operands.subList(1, operands.size())) {
                        addElemIfLocal(op, varTable, use);
                    }
                } else {
                    // new array: use size operand
                    var operands = call.getOperands();
                    for (var op : operands) {
                        addElemIfLocal(op, varTable, use);
                    }
                }
            }
            case BRANCH -> {
                var branch = (CondBranchInstruction) inst;
                for (var op : branch.getOperands()) {
                    addElemIfLocal(op, varTable, use);
                }
            }
            case RETURN -> {
                var ret = (ReturnInstruction) inst;
                ret.getOperand().ifPresent(op -> addElemIfLocal(op, varTable, use));
            }
            case GETFIELD -> {
                var getField = (GetFieldInstruction) inst;
                addElemIfLocal(getField.getObject(), varTable, use);
            }
            case PUTFIELD -> {
                var putField = (PutFieldInstruction) inst;
                addElemIfLocal(putField.getObject(), varTable, use);
                addElemIfLocal(putField.getValue(), varTable, use);
            }
            case GOTO -> { /* no uses */ }
            default -> { /* handle other types */ }
        }
    }

    private static void addElemIfLocal(Element elem, HashMap<String, Descriptor> varTable, Set<String> use) {
        if (elem == null || elem.isLiteral()) return;
        if (elem instanceof ArrayOperand ao) {
            addVarIfLocal(ao.getName(), varTable, use);
            for (var idx : ao.getIndexOperands()) {
                addElemIfLocal(idx, varTable, use);
            }
        } else {
            addVarIfLocal(((Operand) elem).getName(), varTable, use);
        }
    }

    private static void addVarIfLocal(String name, HashMap<String, Descriptor> varTable, Set<String> use) {
        if (isLocalVar(name, varTable)) {
            use.add(name);
        }
    }

    private static boolean isLocalVar(String name, HashMap<String, Descriptor> varTable) {
        var desc = varTable.get(name);
        return desc != null && desc.getScope() == VarScope.LOCAL && !name.equals("this");
    }

    // ---- Interference Graph ----

    private static Map<String, Set<String>> buildInterferenceGraph(
            List<Instruction> instructions,
            HashMap<String, Descriptor> varTable,
            HashMap<Instruction, Set<String>> out,
            List<String> locals) {

        var graph = new HashMap<String, Set<String>>();
        for (var local : locals) {
            graph.put(local, new HashSet<>());
        }

        for (var inst : instructions) {
            Set<String> def = getDef(inst, varTable);
            Set<String> liveOut = out.get(inst);
            if (liveOut == null) liveOut = Set.of();

            for (var v : def) {
                if (!locals.contains(v)) continue;
                for (var u : liveOut) {
                    if (!locals.contains(u) || u.equals(v)) continue;
                    graph.get(v).add(u);
                    graph.get(u).add(v);
                }
            }

            // Also add interference between all live variables at the same time
            // (conservative approach for assignment instruction uses)
            if (inst.getInstType() == InstructionType.ASSIGN) {
                var assign = (AssignInstruction) inst;
                var dest = assign.getDest();
                if (!dest.isLiteral() && dest instanceof ArrayOperand) {
                    // Array assignment - dest is used, not defined
                    // All vars live at out interfere with each other
                    var liveVars = new ArrayList<>(liveOut);
                    for (int i = 0; i < liveVars.size(); i++) {
                        for (int j = i + 1; j < liveVars.size(); j++) {
                            var a = liveVars.get(i);
                            var b = liveVars.get(j);
                            if (locals.contains(a) && locals.contains(b)) {
                                graph.get(a).add(b);
                                graph.get(b).add(a);
                            }
                        }
                    }
                }
            }
        }

        return graph;
    }

    // ---- Graph Coloring ----

    private static int computeMinColorsNeeded(Map<String, Set<String>> graph, List<String> locals) {
        int maxDegree = 0;
        for (var local : locals) {
            maxDegree = Math.max(maxDegree, graph.get(local).size());
        }
        // Start with max degree + 1 and find chromatic number via trial coloring
        for (int k = 1; k <= locals.size(); k++) {
            var result = tryGraphColor(graph, locals, k);
            if (result != null) return k;
        }
        return locals.size();
    }

    private static Map<String, Integer> graphColor(Map<String, Set<String>> graph, List<String> locals, int K) {
        var result = tryGraphColor(graph, locals, K);
        if (result != null) return result;

        // If coloring fails (shouldn't happen for valid OLLIR), use sequential assignment
        var fallback = new HashMap<String, Integer>();
        for (int i = 0; i < locals.size(); i++) {
            fallback.put(locals.get(i), i % Math.max(K, 1));
        }
        return fallback;
    }

    private static Map<String, Integer> tryGraphColor(Map<String, Set<String>> graph, List<String> locals, int K) {
        // Chaitin's algorithm
        var workingGraph = new HashMap<String, Set<String>>();
        for (var local : locals) {
            workingGraph.put(local, new HashSet<>(graph.get(local)));
        }

        var stack = new ArrayDeque<String>();
        var removed = new HashSet<String>();

        while (removed.size() < locals.size()) {
            // Find a node with degree < K
            String toRemove = null;
            for (var local : locals) {
                if (!removed.contains(local)) {
                    var degree = (int) workingGraph.get(local).stream()
                            .filter(n -> !removed.contains(n))
                            .count();
                    if (degree < K) {
                        toRemove = local;
                        break;
                    }
                }
            }

            if (toRemove == null) {
                // Optimistic coloring: pick any remaining node
                for (var local : locals) {
                    if (!removed.contains(local)) {
                        toRemove = local;
                        break;
                    }
                }
            }

            stack.push(toRemove);
            removed.add(toRemove);
        }

        // Assign colors
        var coloring = new HashMap<String, Integer>();
        while (!stack.isEmpty()) {
            var node = stack.pop();
            var usedColors = new HashSet<Integer>();
            for (var neighbor : graph.get(node)) {
                if (coloring.containsKey(neighbor)) {
                    usedColors.add(coloring.get(neighbor));
                }
            }
            // Assign smallest available color
            int color = 0;
            while (usedColors.contains(color)) color++;
            if (color >= K) return null; // Coloring failed
            coloring.put(node, color);
        }

        return coloring;
    }
}
