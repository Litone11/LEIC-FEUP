grammar Javamm;

@header {
    package pt.up.fe.comp2026;
}

CLASS : 'class' ;
EXTENDS : 'extends' ;
INT : 'int' ;
BOOLEAN : 'boolean' ;
VOID : 'void' ;
STATIC : 'static' ;
RETURN : 'return' ;
PACKAGE: 'package';
IMPORT: 'import';
PUBLIC: 'public';
PRIVATE: 'private';
PROTECTED: 'protected';
IF : 'if' ;
ELSE : 'else' ;
ELSEIF : 'elseif' ;
WHILE : 'while' ;
DO : 'do' ;
FOR : 'for' ;
NEW : 'new' ;
THIS : 'this' ;
TRUE : 'true' ;
FALSE : 'false' ;

INTEGER : '0' | [1-9][0-9]* ;
ID : [a-zA-Z_$] [a-zA-Z0-9_$]* ;

WS : [ \t\n\r\f]+ -> skip ;

SINGLE_COMMENT: '//' ~[\r\n]*-> skip;
BLOCK_COMMENT : '/*' .*? '*/' -> skip;

program
    : packageDecl importDecl* classNode=classDecl EOF
    ;

importDecl:
    IMPORT path += ID ('.' path += ID)* ';'
;

//package is mandatory
packageDecl
    : PACKAGE path += ID ('.' path +=ID)* ';'
    ;

classDecl
    : CLASS name=ID (EXTENDS superName=ID)?
        '{'
        (varDecl | methodDecl)*
        '}'
    ;

varDecl
    : typeNode = type name=ID initNode=varInitializer ';'
    ;

varInitializer
    : '=' expr #WithInitializer
    | #NoInitializer
    ;

param
    : typeNode = type name=ID
;

paramsClause
    : param (',' param)* #NonEmptyParams
    | #EmptyParams
    ;

argsClause
    : expr (',' expr)* #NonEmptyArgs
    | #EmptyArgs
    ;

type locals[int arrayDims=0]
    : name=(INT | BOOLEAN | ID) ('[' ']' {$arrayDims++;})*
    | name=VOID
    ;

methodDecl locals[boolean isPublic=false, boolean isStatic=false, String visibility="package-protected"]
    : ((PUBLIC {$isPublic=true; $visibility="public";})
        | (PRIVATE {$visibility="private";})
        | (PROTECTED {$visibility="protected";}))?
        (STATIC {$isStatic=true;})?
        returnType = type name=ID
        '(' paramsNode=paramsClause ')'
        '{' varDecl* stmt* '}'
    ;

forAssign
    : name=ID '=' value=expr
    ;

forInitClause
    : forAssign #PresentForInit
    | #EmptyForInit
    ;

forConditionClause
    : expr #PresentForCondition
    | #EmptyForCondition
    ;

forUpdateClause
    : forAssign #PresentForUpdate
    | #EmptyForUpdate
    ;

stmt
    : '{' stmt* '}' #CompoundStmt
    | IF '(' expr ')' stmt ELSEIF {notifyErrorListeners("Use 'else if' instead of 'elseif'");} stmt (ELSE stmt)? #IfElseIfErrorStmt
    | IF '(' expr ')' stmt ELSE stmt #IfElseStmt
    | IF '(' expr ')' stmt {_input.LA(1) != ELSEIF}? #IfStmt
    | WHILE '(' expr ')' stmt #WhileStmt
    | DO stmt WHILE '(' expr ')' ';' #DoWhileStmt
    | FOR '(' initNode=forInitClause ';' conditionNode=forConditionClause ';' updateNode=forUpdateClause ')' stmt #ForStmt
    | array=ID ('[' index+=expr ']')+ '=' value=expr ';' #ArrayAssignStmt
    | var = ID '=' expr ';' #AssignStmt
    | expr ';' #ExprStmt
    | RETURN expr? ';' #ReturnStmt
    ;

expr locals[int arrayDims=0]
    : op=('!' | '+' | '-' | '++' | '--') expr #UnaryExpr
    | '(' expr ')' #ParenExpr
    | NEW INT '[' size+=expr ']' {$arrayDims++;} ('[' size+=expr ']' {$arrayDims++;})* ('[' ']' {$arrayDims++;})* #NewArrayExpr
    | NEW INT '[' ']' '{' argsNode=argsClause '}' #ArrayInitExpr
    | NEW name=ID '(' argsNode=argsClause ')' #NewObjectExpr
    | value=INTEGER #IntegerLiteral
    | value=TRUE #BooleanLiteral
    | value=FALSE #BooleanLiteral
    | THIS #ThisExpr
    | method=ID '(' argsNode=argsClause ')' #ImplicitThisCallExpr
    | name=ID #VarRefExpr
    | expr '[' expr ']' #ArrayAccessExpr
    | target=expr '.' method=ID '(' argsNode=argsClause ')' #MethodCallExpr
    | expr '.' 'length' #LengthExpr
    | expr '.' field=ID #FieldAccessExpr
    | expr op=('*' | '/' | '%') expr #BinaryExpr
    | expr op=('+' | '-') expr #BinaryExpr
    | expr op=('<' | '>' | '<=' | '>=') expr #BinaryExpr
    | expr op=('==' | '!=') expr #BinaryExpr
    | expr op='&&' expr #BinaryExpr
    | expr op='||' expr #BinaryExpr
    ;
