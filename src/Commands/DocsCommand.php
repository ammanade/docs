<?php

namespace Ammanade\Docs\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route as RouteFacade;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;
use Symfony\Component\Console\Attribute\AsCommand;
use Ammanade\Docs\Attributes\Input;
use Ammanade\Docs\Attributes\Output;

#[AsCommand('docs:generate')]
class DocsCommand extends Command
{
    protected $signature = 'docs:generate';
    protected $description = 'Generate API documentation based on route attributes';

    public function handle(): int
    {
        $routes = collect(RouteFacade::getRoutes());

        $docs = $routes
            ->map(function ($route) {
                $action = $route->getAction('uses');

                if (!is_string($action) || !str_contains($action, '@')) {
                    return null;
                }

                [$controller, $method] = explode('@', $action);

                if (!class_exists($controller) || !method_exists($controller, $method)) {
                    return null;
                }

                $reflectionMethod = new \ReflectionMethod($controller, $method);

                $inputClass = null;
                $outputClass = null;

                foreach ($reflectionMethod->getAttributes() as $attribute) {
                    $instance = $attribute->newInstance();

                    if ($instance instanceof Input) {
                        $inputClass = $instance->class;
                    }

                    if ($instance instanceof Output) {
                        $outputClass = $instance->class;
                    }
                }

                if (!$inputClass || !$outputClass) {
                    return null;
                }

                $inputFields = $this->extractFields($inputClass);
                $outputFields = $this->extractFields($outputClass);

                return [
                    'method' => $route->methods()[0] ?? 'GET',
                    'uri' => '/' . ltrim($route->uri(), '/'),
                    'controller' => $controller,
                    'action' => $method,
                    'input' => [
                        'class' => $inputClass,
                        'fields' => $inputFields,
                    ],
                    'output' => [
                        'class' => $outputClass,
                        'fields' => $outputFields,
                    ],
                ];
            })
            ->filter()
            ->values()
            ->toArray();

        file_put_contents(
            __DIR__ . '/../../docs.json',
            json_encode($docs, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );

        $this->info('✅ Документация успешно сгенерирована');

        return self::SUCCESS;
    }

    private function extractFields(string $className): array
    {
        if (!class_exists($className)) {
            return [];
        }

        $reflection = new ReflectionClass($className);

        return collect($reflection->getProperties(ReflectionProperty::IS_PUBLIC))
            ->mapWithKeys(function (ReflectionProperty $property) use ($reflection): array {
                $type = $property->getType();
                $typeName = $type instanceof ReflectionNamedType
                    ? $type->getName()
                    : 'mixed';

                // Если тип - array, пробуем прочитать @var
                if ($typeName === 'array') {
                    $docComment = $property->getDocComment();
                    if ($docComment && preg_match('/@var\s+([^\s]+)/', $docComment, $matches)) {
                        $docType = trim($matches[1]);
                        $isArray = str_ends_with($docType, '[]');
                        $subType = $isArray ? substr($docType, 0, -2) : $docType;

                        // Получаем полное имя класса с учетом импортов
                        $subTypeFull = $this->resolveFullQualifiedName($subType, $reflection);

                        if (class_exists($subTypeFull)) {
                            return [
                                $property->getName() => [
                                    'type' => 'array',
                                    'items' => $isArray
                                        ? [
                                            'type' => 'object',
                                            'properties' => $this->extractFields($subTypeFull),
                                        ]
                                        : $this->extractFields($subTypeFull),
                                ],
                            ];
                        }

                        return [
                            $property->getName() => [
                                'type' => 'array',
                                'items' => [
                                    'type' => $subType,
                                ],
                            ]
                        ];
                    }
                }

                // Если это объект
                if (class_exists($typeName)) {
                    return [
                        $property->getName() => [
                            'type' => 'object',
                            'properties' => $this->extractFields($typeName),
                        ],
                    ];
                }

                // Иначе примитив
                return [
                    $property->getName() => [
                        'type' => $typeName,
                    ],
                ];
            })
            ->toArray();
    }

    private function resolveFullQualifiedName(string $class, ReflectionClass $context): string
    {
        // Если класс уже полный
        if (class_exists($class)) {
            return $class;
        }

        // Проверяем импорты класса
        $imports = $this->getImportedClasses($context);
        if (isset($imports[$class])) {
            return $imports[$class];
        }

        // Пробуем достроить через текущий namespace
        $namespace = $context->getNamespaceName();
        $full = $namespace . '\\' . $class;

        if (class_exists($full)) {
            return $full;
        }

        return $class; // fallback
    }

    private function getImportedClasses(ReflectionClass $context): array
    {
        $imports = [];
        $content = file_get_contents($context->getFileName());

        if (preg_match_all('/^use\s+([^\s;]+)/m', $content, $matches)) {
            foreach ($matches[1] as $import) {
                $parts = explode('\\', $import);
                $shortName = end($parts);
                $imports[$shortName] = $import;

                // Также добавляем алиасы, если они есть
                if (preg_match('/^use\s+([^\s]+)\s+as\s+([^\s;]+)/m', $import, $aliasMatches)) {
                    $imports[$aliasMatches[2]] = $aliasMatches[1];
                }
            }
        }

        return $imports;
    }
}