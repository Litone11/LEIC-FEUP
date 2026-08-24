package core.jasmin;

import org.junit.Test;
import pt.up.fe.specs.util.SpecsIo;

import java.io.File;
import java.util.List;

import static org.junit.Assert.assertEquals;
import static org.junit.Assert.assertTrue;
import static org.junit.Assert.assertFalse;

public class MyJasminSmokeTest {

    private pt.up.fe.comp.jmm.jasmin.JasminResult compile(String jmmFilePath) {
        var file = new File(jmmFilePath);
        var source = SpecsIo.read(file);
        var config = new java.util.HashMap<String, String>();
        config.put("optimize", "false");
        config.put("registerAllocation", "-1");
        config.put("inputFile", file.getAbsolutePath());
        var lex = new pt.up.fe.comp2026.lexer.JmmLexerImpl().lex(source, config);
        var parse = new pt.up.fe.comp2026.parser.JmmParserImpl().parse(lex, config);
        var sema = new pt.up.fe.comp2026.analysis.JmmAnalysisImpl().semanticAnalysis(parse);
        var ollir = new pt.up.fe.comp2026.optimization.JmmOptimizationImpl().toOllir(sema);
        return new pt.up.fe.comp2026.backend.JasminBackendImpl().toJasmin(ollir);
    }

    @Test
    public void factorialAssemblesWithoutHardcodedLimits() {
        var jasmin = compile("test/core/semantics/symboltable/Factorial.jmm");
        var code = jasmin.getJasminCode();
        assertTrue("public class", code.contains(".class public"));
        assertFalse("no hardcoded .limit stack 99", code.contains(".limit stack 99"));
        assertFalse("no hardcoded .limit locals 99", code.contains(".limit locals 99"));
        assertTrue("iconst used", code.contains("iconst_"));
        assertTrue("bipush used", code.contains("bipush 10"));
        assertTrue("if_icmplt for compare", code.contains("if_icmplt") || code.contains("iflt"));
        assertTrue("invokestatic", code.contains("invokestatic"));
        assertTrue("invokevirtual", code.contains("invokevirtual"));
        assertTrue("invokespecial", code.contains("invokespecial"));
        assertTrue("new + class", code.contains("new ") && code.contains("Factorial"));
        assertTrue("aload_0", code.contains("aload_0"));
        assertTrue("istore short form", code.contains("istore_") || code.contains("astore_"));
    }

    @Test
    public void factorialExecutesCorrectly() {
        var jasmin = compile("test/core/semantics/symboltable/Factorial.jmm");
        var execution = jasmin.run(List.of(), List.of("libs-jmm/compiled"));
        var stdout = execution.getStdout().trim();
        var stderr = execution.getStderr();
        assertEquals("non-zero return: stdout=" + stdout + " stderr=" + stderr, 0, execution.getReturnCode());
        assertTrue("expected 3628800 in output, got: " + stdout, stdout.contains("3628800"));
    }
}
