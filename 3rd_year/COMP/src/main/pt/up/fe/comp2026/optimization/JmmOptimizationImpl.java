package pt.up.fe.comp2026.optimization;

import pt.up.fe.comp.jmm.analysis.JmmSemanticsResult;
import pt.up.fe.comp.jmm.ollir.JmmOptimization;
import pt.up.fe.comp.jmm.ollir.OllirResult;

import java.util.Collections;

public class JmmOptimizationImpl implements JmmOptimization {

    @Override
    public OllirResult toOllir(JmmSemanticsResult semanticsResult) {
        var visitor = new OllirGeneratorVisitor(semanticsResult.getSymbolTable());
        var ollirCode = visitor.visit(semanticsResult.getRootNode());
        return new OllirResult(semanticsResult, ollirCode, Collections.emptyList());
    }

    @Override
    public JmmSemanticsResult transformAst(JmmSemanticsResult semanticsResult) {
        var config = semanticsResult.config();
        boolean optimize = config.getOrDefault("optimize", "false").equals("true")
                || config.getOrDefault("o", "false").equals("true");

        if (!optimize) return semanticsResult;

        var root = semanticsResult.getRootNode();
        var propVisitor = new ConstantPropagationVisitor();
        var foldVisitor = new ConstantFoldingVisitor();
        var branchVisitor = new BranchEliminationVisitor();
        var deadCodeVisitor = new DeadCodeEliminationVisitor(semanticsResult.getSymbolTable());

        // Iterate until fixed point
        boolean changed = true;
        while (changed) {
            changed = false;
            if (propVisitor.propagate(root)) changed = true;
            if (foldVisitor.fold(root)) changed = true;
            if (branchVisitor.eliminate(root)) changed = true;
            if (deadCodeVisitor.eliminate(root)) changed = true;
        }

        return semanticsResult;
    }

    @Override
    public OllirResult transformOllir(OllirResult ollirResult) {
        if (ollirResult.config().getOrDefault("debug", "false").equals("true")) {
            System.out.println("OLLIR CODE:");
            System.out.println(ollirResult.getOllirCode());
        }

        var config = ollirResult.config();
        String regAllocStr = config.getOrDefault("registerAllocation", null);
        if (regAllocStr == null) return ollirResult;

        int maxRegisters;
        try {
            maxRegisters = Integer.parseInt(regAllocStr);
        } catch (NumberFormatException e) {
            return ollirResult;
        }

        var classUnit = ollirResult.getOllirClass();
        RegisterAllocator.allocate(classUnit, maxRegisters);

        return ollirResult;
    }
}
