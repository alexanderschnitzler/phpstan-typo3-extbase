<?php

declare(strict_types=1);

namespace Schnitzler\PHPStan\TYPO3\Extbase\Type\QueryResult;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\ArrayType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use TYPO3\CMS\Extbase\Persistence\Generic\Query;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

class ExecuteDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return Query::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getName() === 'execute';
    }

    public function getTypeFromMethodCall(
        MethodReflection $methodReflection,
        MethodCall $methodCall,
        Scope $scope
    ): \PHPStan\Type\Type {
        /** @var Variable $callingVariable */
        $callingVariable = $methodCall->var;
        $callingVariableName = (string)$callingVariable->name;

        $types = [new MixedType()];
        $variableType = $scope->getVariableType($callingVariableName);
        if ($variableType instanceof GenericObjectType && $variableType->getTypes() !== []) {
            $types = $variableType->getTypes();
        }

        if ($this->evaluateArgumentValue($methodCall) === false) {
            return new GenericObjectType(QueryResultInterface::class, $types);
        } else {
            return new ArrayType(new IntegerType(), reset($types));
        }
    }

    private function evaluateArgumentValue(MethodCall $methodCall): bool
    {
        $defaultArgument = false;

        if (!($argument = $methodCall->args[0] ?? null) instanceof Arg) {
            return $defaultArgument;
        }

        if (!($constFetch = $argument->value ?? null) instanceof ConstFetch) {
            return $defaultArgument;
        }

        /** @var ConstFetch $constFetch */

        $argumentValue = $constFetch->name->toLowerString();

        return $argumentValue === 'true';
    }
}
