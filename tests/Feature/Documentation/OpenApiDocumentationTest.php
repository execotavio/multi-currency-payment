<?php

namespace Tests\Feature\Documentation;

use Symfony\Component\Yaml\Yaml;
use Tests\TestCase;

class OpenApiDocumentationTest extends TestCase
{
    private array $document;

    protected function setUp(): void
    {
        parent::setUp();

        $this->document = Yaml::parseFile(base_path('docs/openapi.yaml'));
    }

    public function test_openapi_document_has_required_top_level_sections(): void
    {
        $this->assertSame('3.1.0', $this->document['openapi']);
        $this->assertArrayHasKey('info', $this->document);
        $this->assertArrayHasKey('servers', $this->document);
        $this->assertArrayHasKey('components', $this->document);
        $this->assertArrayHasKey('security', $this->document);
        $this->assertArrayHasKey('paths', $this->document);
    }

    public function test_it_documents_all_implemented_paths_and_operations(): void
    {
        $expectedOperations = [
            '/api/auth/register' => ['post'],
            '/api/auth/login' => ['post'],
            '/api/auth/logout' => ['post'],
            '/api/auth/finance-only' => ['get'],
            '/api/payment-requests' => ['get', 'post'],
            '/api/payment-requests/{paymentRequest}' => ['get'],
            '/api/payment-requests/{paymentRequest}/approve' => ['post'],
            '/api/payment-requests/{paymentRequest}/reject' => ['post'],
        ];

        foreach ($expectedOperations as $path => $methods) {
            $this->assertArrayHasKey($path, $this->document['paths']);

            foreach ($methods as $method) {
                $operation = $this->document['paths'][$path][$method] ?? null;

                $this->assertIsArray($operation, "{$method} {$path} is missing");
                $this->assertArrayHasKey('summary', $operation);
                $this->assertArrayHasKey('operationId', $operation);
                $this->assertArrayHasKey('responses', $operation);
            }
        }
    }

    public function test_it_documents_security_and_reusable_schemas(): void
    {
        $this->assertSame([
            'type' => 'http',
            'scheme' => 'bearer',
            'bearerFormat' => 'Passport',
        ], $this->document['components']['securitySchemes']['bearerAuth']);

        foreach (['PaymentRequest', 'User', 'Error', 'ValidationError', 'ProviderError'] as $schema) {
            $this->assertArrayHasKey($schema, $this->document['components']['schemas']);
        }
    }

    public function test_payment_request_creation_documents_provider_bad_gateway(): void
    {
        $responses = $this->document['paths']['/api/payment-requests']['post']['responses'];

        $this->assertArrayHasKey('502', $responses);
        $this->assertSame(
            '#/components/responses/ProviderBadGateway',
            $responses['502']['$ref'],
        );
    }

    public function test_payment_request_examples_include_currency_conversion_fields(): void
    {
        $paymentRequest = $this->document['components']['schemas']['PaymentRequest'];

        $this->assertSame('20.00', $paymentRequest['properties']['amount_eur']['example']);
        $this->assertSame('exchangerate-api', $paymentRequest['properties']['rate_source']['example']);

        $createExample = $this->document['paths']['/api/payment-requests']['post']['responses']['201']
            ['content']['application/json']['example'];

        $this->assertSame('20.00', $createExample['amount_eur']);
        $this->assertSame('exchangerate-api', $createExample['rate_source']);
        $this->assertSame('5.500000', $createExample['eur_to_local_rate']);
    }
}
