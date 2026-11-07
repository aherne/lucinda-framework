<?php

/**
 * Checks persistence dependencies, imperative syntax and public DAO contracts.
 */

require dirname(__DIR__) . '/vendor/autoload.php';
$root = dirname(__DIR__);
$errors = [];
$directory = new RecursiveDirectoryIterator($root . '/src');
$iterator = new RecursiveIteratorIterator($directory);
foreach ($iterator as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }
    $path = str_replace(
        '\\',
        '/',
        substr($file->getPathname(), strlen($root) + 1)
    );
    $source = file_get_contents($file->getPathname());
    $tokens = token_get_all($source);
    $code = '';
    $strings = [];
    $literalContentWidths = [];
    foreach ($tokens as $token) {
        if (!is_array($token)) {
            $code .= $token;
            continue;
        }
        if (in_array($token[0], [T_COMMENT, T_DOC_COMMENT], true)) {
            continue;
        }
        if (
            $token[0] === T_CONSTANT_ENCAPSED_STRING
            || $token[0] === T_ENCAPSED_AND_WHITESPACE
        ) {
            $strings[] = $token[1];
            if (
                $token[0] === T_CONSTANT_ENCAPSED_STRING
                && !str_contains($token[1], "\n")
            ) {
                $literalContentWidths[$token[2]] =
                    ($literalContentWidths[$token[2]] ?? 0)
                    + strlen($token[1]) - 2;
            }
            $code .= '""';
            continue;
        }
        $code .= $token[1];
    }
    preg_match('/namespace\s+([^;]+);/', $code, $namespaceMatch);
    $namespace = trim($namespaceMatch[1] ?? '');
    $imports = [];
    preg_match_all(
        '/\buse\s+([\\\\\w]+)(?:\s+as\s+(\w+))?\s*;/',
        $code,
        $matches,
        PREG_SET_ORDER
    );
    foreach ($matches as $match) {
        $parts = explode('\\', $match[1]);
        $imports[$match[2] ?? end($parts)] = ltrim($match[1], '\\');
    }
    $references = [];
    foreach ($tokens as $token) {
        if (
            !is_array($token) || !in_array($token[0], [
            T_NAME_QUALIFIED,
            T_NAME_FULLY_QUALIFIED,
            T_STRING,
            ], true)
        ) {
            continue;
        }
        $name = ltrim($token[1], '\\');
        $parts = explode('\\', $name);
        if (isset($imports[$parts[0]])) {
            $base = array_shift($parts);
            $name = $imports[$base] . ($parts ? '\\' . implode(
                '\\',
                $parts
            ) : '');
        } elseif (
            $token[0] !== T_NAME_FULLY_QUALIFIED && !str_starts_with(
                $name,
                'Lucinda\\'
            )
        ) {
            $name = $namespace . '\\' . $name;
        }
        $references[] = $name;
    }
    $inQueries = str_starts_with($path, 'src/Persistence/Queries/');
    $isExecutor = in_array($path, [
        'src/Persistence/SqlExecutor.php',
        'src/Persistence/RedisExecutor.php',
    ], true);

    $inRefactorScope = str_starts_with($path, 'src/DAO/')
        || str_starts_with($path, 'src/Persistence/')
        || str_starts_with($path, 'src/Presentation/');
    if ($inRefactorScope) {
        foreach (explode("\n", $source) as $lineNumber => $line) {
            $lineWidth = strlen(rtrim($line, "\r"));
            if ($inQueries) {
                // Keep SQL literals whole; wrap the surrounding PHP instead.
                $lineWidth -= $literalContentWidths[$lineNumber + 1] ?? 0;
            }
            if ($lineWidth > 80) {
                $errors[] = "$path:" . ($lineNumber + 1)
                    . ': line exceeds 80 characters';
            }
        }
        foreach ($tokens as $index => $token) {
            if (!is_array($token)) {
                continue;
            }
            if (
                in_array($token[0], [
                T_STATIC,
                T_FN,
                T_CALLABLE,
                T_ELLIPSIS,
                ], true)
            ) {
                $errors[] = "$path: static or functional syntax is forbidden";
            }
            if ($token[0] === T_FUNCTION) {
                $next = $index + 1;
                while (
                    isset($tokens[$next]) && is_array($tokens[$next])
                    && $tokens[$next][0] === T_WHITESPACE
                ) {
                    $next++;
                }
                if (($tokens[$next] ?? null) === '(') {
                    $errors[] = "$path: anonymous function is forbidden";
                }
            }
            if ($token[0] === T_DOUBLE_COLON) {
                $before = $index - 1;
                $after = $index + 1;
                while (
                    isset($tokens[$before]) && is_array($tokens[$before])
                    && $tokens[$before][0] === T_WHITESPACE
                ) {
                    $before--;
                }
                while (
                    isset($tokens[$after]) && is_array($tokens[$after])
                    && $tokens[$after][0] === T_WHITESPACE
                ) {
                    $after++;
                }
                $name = $tokens[$after][1] ?? '';
                $after++;
                while (
                    isset($tokens[$after]) && is_array($tokens[$after])
                    && $tokens[$after][0] === T_WHITESPACE
                ) {
                    $after++;
                }
                $owner = $tokens[$before][1] ?? '';
                if (
                    ($tokens[$after] ?? null) === '('
                    && $owner !== 'parent'
                ) {
                    $errors[] = "$path: unapproved static method call";
                }
            }
        }
        if (
            preg_match(
                '/\b(?:array_map|array_filter|array_walk|array_reduce|usort|uasort|uksort|call_user_func\w*)\s*\(/',
                $code
            )
        ) {
            $errors[] = "$path: callback-oriented operation is forbidden";
        }
        if (preg_match('/\$\w+\s*\(/', $code)) {
            $errors[] = "$path: invoking a callable variable is forbidden";
        }
    }


    if ($inQueries) {
        $awaitingConstructor = false;
        $constructorDepth = 0;
        foreach ($tokens as $index => $token) {
            if ($token === '{') {
                if ($awaitingConstructor || $constructorDepth > 0) {
                    $constructorDepth++;
                    $awaitingConstructor = false;
                }
                continue;
            }
            if ($token === '}' && $constructorDepth > 0) {
                $constructorDepth--;
                continue;
            }
            if (!is_array($token)) {
                continue;
            }
            if ($token[0] === T_FUNCTION) {
                $next = $index + 1;
                while (
                    isset($tokens[$next]) && is_array($tokens[$next])
                    && $tokens[$next][0] === T_WHITESPACE
                ) {
                    $next++;
                }
                $awaitingConstructor = ($tokens[$next][1] ?? '')
                    === '__construct';
            }
            if ($constructorDepth === 0) {
                continue;
            }
            if ($token[0] === T_DOUBLE_COLON) {
                $previous = $tokens[$index - 1][1] ?? '';
                if ($previous === 'parent') {
                    $errors[] = "$path: assign query state directly";
                }
            }
            if ($token[0] === T_OBJECT_OPERATOR) {
                $next = $index + 1;
                while (
                    isset($tokens[$next]) && is_array($tokens[$next])
                    && $tokens[$next][0] === T_WHITESPACE
                ) {
                    $next++;
                }
                $method = $tokens[$next][1] ?? '';
                if (
                    in_array($method, [
                    'fields', 'where', 'columns', 'values', 'set',
                    'joinInner', 'joinLeft', 'joinRight', 'joinCross',
                    'onDuplicateKeyUpdate', 'limit', 'groupBy', 'orderBy',
                    ], true)
                ) {
                    $errors[] = "$path: construct clauses in focused methods";
                }
            }
        }
    }

    $isProviderSetup = in_array($path, [
        'src/EventListeners/SQLDataSource.php',
        'src/EventListeners/NoSQLDataSource.php',
    ], true);
    foreach (array_unique($references) as $reference) {
        if (
            in_array(ltrim($reference, '\\'), [
            'PDO',
            'Redis',
            'Memcached',
            'mysqli',
            ], true) && !$isExecutor
        ) {
            $errors[] = "$path: native driver outside executor";
        }
        if (
            $inQueries && (str_starts_with(
                $reference,
                'Lucinda\\Project\\DAO\\'
            ) || str_contains(
                $reference,
                '\\Builders\\'
            ) || str_contains(
                $reference,
                '\\Entities\\'
            ) || str_ends_with(
                $reference,
                'Executor'
            ) || $reference === 'Lucinda\\Framework\\ServiceRegistry')
        ) {
            $errors[] = "$path: query depends on execution or application "
                . "state";
        }
        if (str_starts_with($reference, 'Lucinda\\Query\\') && !$inQueries) {
            $errors[] = "$path: query construction outside Queries";
        }
        if (
            (str_starts_with(
                $reference,
                'Lucinda\\SQL\\'
            )
                || str_starts_with($reference, 'Lucinda\\NoSQL\\')
            ) && !$isExecutor
        ) {
            // Exception types expose failures, not driver access.
            if (!str_ends_with($reference, 'Exception')) {
                $errors[] = "$path: driver dependency outside executor";
            }
        }
        if (
            in_array($reference, [
            'Lucinda\\Framework\\SqlConnectionProvider',
            'Lucinda\\Framework\\NoSqlDriverProvider',
            ], true) && !$isExecutor && !$isProviderSetup
        ) {
            $errors[] = "$path: connection provider outside execution/setup "
                . "boundary";
        }
        if (
            str_starts_with(
                $path,
                'src/Persistence/Builders/'
            ) && (str_contains(
                $reference,
                '\\Queries\\'
            ) || str_ends_with(
                $reference,
                'Executor'
            ) || str_starts_with($reference, 'Lucinda\\Project\\DAO\\'))
        ) {
            $errors[] = "$path: builder depends on orchestration/execution";
        }
        if (
            str_starts_with(
                $path,
                'src/Persistence/Entities/'
            ) && (str_starts_with(
                $reference,
                'Lucinda\\Project\\DAO\\'
            ) || str_contains(
                $reference,
                '\\Queries\\'
            ) || str_ends_with($reference, 'Executor'))
        ) {
            $errors[] = "$path: entity depends on persistence operations";
        }
        if (
            (str_starts_with(
                $path,
                'src/Controllers/'
            ) || str_starts_with(
                $path,
                'src/ViewResolvers/'
            )) && str_starts_with($reference, 'Lucinda\\Project\\Persistence\\')
        ) {
            $errors[] = "$path: presentation depends on persistence";
        }
    }
    if (preg_match('/(?<![\w$])\\\\?(?:SQL|NoSQL)\s*\(/i', $code)) {
        $errors[] = "$path: deleted driver helper call";
    }
    if (
        !$isExecutor && preg_match(
            '/->\s*(?:getConnection|preparedStatement|statement|connect|query|exec)\s*\(/',
            $code
        )
    ) {
        $errors[] = "$path: direct driver execution";
    }
    foreach ($strings as $string) {
        if ($inQueries && preg_match('/\bAS\s+[a-z_]/i', $string)) {
            $errors[] = "$path: field aliases must use Fields::add";
        }

        if (
            preg_match(
                '/\b(?:SELECT\s+.+?\s+FROM|INSERT\s+(?:IGNORE\s+)?INTO|'
                . 'UPDATE\s+\w+\s+SET|DELETE\s+FROM|CREATE\s+TABLE|'
                . 'DROP\s+TABLE|TRUNCATE\s+TABLE)\b/is',
                $string
            )
        ) {
            $errors[] = "$path: Hand-written SQL statement (use "
                . "lucinda/queries)";
        }
    }
    if (
        preg_match(
            '/namespace\s+([^;]+);/',
            $code,
            $ns
        ) && preg_match('/\b(?:class|interface|enum)\s+(\w+)/', $code, $class)
    ) {
        $fqcn = trim($ns[1]) . '\\' . $class[1];
        if (!class_exists($fqcn) && !interface_exists($fqcn)) {
            continue;
        }
        $reflection = new ReflectionClass($fqcn);
        foreach ($reflection->getProperties() as $property) {
            if (
                $property->getDeclaringClass()->getName() === $fqcn
                && $property->isPublic()
            ) {
                $errors[] = "$path: public fields are forbidden; use getters";
            }
        }
        foreach ($reflection->getMethods() as $method) {
            if (
                $method->getDeclaringClass()->getName() === $fqcn
                && in_array($method->getName(), ['__get', '__set'], true)
            ) {
                $errors[] = "$path: magic property access is forbidden";
            }
        }
        if ($fqcn === 'Lucinda\Project\Persistence\Queries\Query') {
            if (
                !$reflection->isAbstract()
                || $reflection->getConstructor() !== null
            ) {
                $errors[] = "$path: query base must not construct operations";
            }
            foreach (['statement', 'parameters'] as $field) {
                if (!$reflection->getProperty($field)->isProtected()) {
                    $errors[] = "$path: query assembly fields must be protected";
                }
            }
        }

        if ($inRefactorScope) {
            if ($reflection->getDocComment() === false) {
                $errors[] = "$path: class documentation is required";
            }
            foreach ($reflection->getProperties() as $property) {
                if ($property->getDeclaringClass()->getName() !== $fqcn) {
                    continue;
                }
                if ($property->getDocComment() === false) {
                    $errors[] = "$path: missing owned-property documentation";
                }
            }
            foreach ($reflection->getMethods() as $method) {
                if ($method->getDeclaringClass()->getName() !== $fqcn) {
                    continue;
                }
                if ($method->getDocComment() === false) {
                    $errors[] = "$path: method documentation is required";
                }
            }
        }

        if (str_starts_with($path, 'src/Persistence/Entities/')) {
            foreach ($reflection->getProperties() as $property) {
                if (!$property->isPrivate()) {
                    $errors[] = "$path: entity fields must be private";
                }
            }

            if (
                !$reflection->isFinal()
                || !$reflection->isReadOnly()
                || $reflection->implementsInterface(ArrayAccess::class)
            ) {
                $errors[] = "$path: entities must be final, readonly and not "
                    . "ArrayAccess";
            }
            foreach ($reflection->getProperties() as $property) {
                if (!$property->hasType()) {
                    $errors[] = "$path: entity property lacks a type";
                }
            }
        }
        if (str_starts_with($path, 'src/DAO/')) {
            $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
            foreach ($methods as $method) {
                if ($method->isConstructor() || $method->isDestructor()) {
                    continue;
                }
                $return = (string) $method->getReturnType();
                if (
                    $return === '' || in_array($return, [
                    'mixed',
                    'object',
                    'iterable',
                    'Traversable',
                    ], true) || str_contains(
                        $return,
                        'Lucinda\\Project\\Persistence\\'
                    )
                ) {
                    $errors[] = "$path: DAO public method exposes an "
                        . "unbounded/internal result: " . $method->getName();
                }
                if (
                    $return === 'array' && (!preg_match(
                        '/@return\s+(?:list<|array<|[\\\\\w]+\[\])/',
                        $method->getDocComment() ?: ''
                    ) || str_contains(
                        $method->getDocComment() ?: '',
                        'Persistence\\Entities'
                    ))
                ) {
                    $errors[] = "$path: DAO collection must document outward "
                        . "element types: " . $method->getName();
                }
            }
        }
    }
}
$directory = new RecursiveDirectoryIterator($root . '/templates');
$templates = new RecursiveIteratorIterator($directory);
foreach ($templates as $file) {
    if (!$file->isFile()) {
        continue;
    }
    if (
        preg_match(
            '/Lucinda\\\\Project\\\\Persistence\\\\|\b(?:SQL|NoSQL)\s*\(/i',
            file_get_contents($file->getPathname())
        )
    ) {
        $errors[] = $file->getPathname()
            . ': persistence reference in template';
    }
}
$errors = array_values(array_unique($errors));
if ($errors) {
    fwrite(STDERR, implode(PHP_EOL, $errors) . PHP_EOL);
    exit(1);
}
echo "Persistence architecture checks passed.\n";
