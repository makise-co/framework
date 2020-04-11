<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Testing\Http;

use MakiseCo\Testing\Assert\Assert as PHPUnit;
use MakiseCo\Testing\Assert\SeeInOrder;
use MakiseCo\Util\Arr;
use MakiseCo\Util\PropertyAccessorHelper;
use MakiseCo\Util\Str;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

use function time;

class TestResponse
{
    /**
     * @var ResponseInterface|\MakiseCo\Http\Response
     */
    protected ResponseInterface $response;

    protected PropertyAccessorInterface $propertyAccessor;

    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
        $this->propertyAccessor = PropertyAccess::createPropertyAccessorBuilder()
            ->disableExceptionOnInvalidIndex()
            ->disableExceptionOnInvalidPropertyPath()
            ->getPropertyAccessor();
    }

    /**
     * Assert that the response has a successful status code.
     *
     * @return $this
     */
    public function assertSuccessful(): self
    {
        $code = $this->response->getStatusCode();

        PHPUnit::assertTrue(
            200 >= $code && $code < 400,
            'Response status code [' . $code . '] is not a successful status code.'
        );

        return $this;
    }

    /**
     * Assert that the response has a 200 status code.
     *
     * @return $this
     */
    public function assertOk(): self
    {
        PHPUnit::assertSame(
            200, $this->response->getStatusCode(),
            'Response status code [' . $this->response->getStatusCode() . '] does not match expected 200 status code.'
        );

        return $this;
    }

    /**
     * Assert that the response has a 201 status code.
     *
     * @return $this
     */
    public function assertCreated(): self
    {
        $actual = $this->response->getStatusCode();

        PHPUnit::assertSame(
            201, $actual,
            "Response status code [{$actual}] does not match expected 201 status code."
        );

        return $this;
    }

    /**
     * Assert that the response has the given status code and no content.
     *
     * @param int $status
     * @return $this
     */
    public function assertNoContent($status = 204): self
    {
        $this->assertStatus($status);

        PHPUnit::assertEmpty($this->response->getContent(), 'Response content is not empty.');

        return $this;
    }

    /**
     * Assert that the response has a not found status code.
     *
     * @return $this
     */
    public function assertNotFound(): self
    {
        PHPUnit::assertSame(
            404, $this->response->getStatusCode(),
            'Response status code [' . $this->response->getStatusCode() . '] is not a not found status code.'
        );

        return $this;
    }

    /**
     * Assert that the response has a forbidden status code.
     *
     * @return $this
     */
    public function assertForbidden(): self
    {
        PHPUnit::assertSame(
            403, $this->response->getStatusCode(),
            'Response status code [' . $this->response->getStatusCode() . '] is not a forbidden status code.'
        );

        return $this;
    }

    /**
     * Assert that the response has an unauthorized status code.
     *
     * @return $this
     */
    public function assertUnauthorized(): self
    {
        $actual = $this->response->getStatusCode();

        PHPUnit::assertSame(
            401, $actual,
            "Response status code [{$actual}] is not an unauthorized status code."
        );

        return $this;
    }

    /**
     * Assert that the response has the given status code.
     *
     * @param int $status
     * @return $this
     */
    public function assertStatus($status): self
    {
        $actual = $this->response->getStatusCode();

        PHPUnit::assertSame(
            $actual, $status,
            "Expected status code {$status} but received {$actual}."
        );

        return $this;
    }

    /**
     * Asserts that the response contains the given header and equals the optional value.
     *
     * @param string $headerName
     * @param mixed $value
     * @return $this
     */
    public function assertHeader($headerName, $value = null): self
    {
        PHPUnit::assertTrue(
            $this->response->hasHeader($headerName), "Header [{$headerName}] not present on response."
        );

        $actual = $this->response->getHeader($headerName);

        if (null !== $value) {
            PHPUnit::assertEquals(
                $value, $actual,
                "Header [{$headerName}] was found, but value [{$actual}] does not match [{$value}]."
            );
        }

        return $this;
    }

    /**
     * Asserts that the response does not contains the given header.
     *
     * @param string $headerName
     * @return $this
     */
    public function assertHeaderMissing($headerName): self
    {
        PHPUnit::assertFalse(
            $this->response->hasHeader($headerName), "Unexpected header [{$headerName}] is present on response."
        );

        return $this;
    }

    /**
     * Assert that the current location header matches the given URI.
     *
     * @param string $uri
     * @return $this
     */
    public function assertLocation($uri): self
    {
//        PHPUnit::assertEquals(
//            app('url')->to($uri), app('url')->to($this->headers->get('Location'))
//        );

        return $this;
    }

    /**
     * Asserts that the response contains the given cookie and equals the optional value.
     *
     * @param string $cookieName
     * @param mixed $value
     * @return $this
     */
    public function assertCookie($cookieName, $value = null): self
    {
        PHPUnit::assertNotNull(
            $cookie = $this->getCookie($cookieName),
            "Cookie [{$cookieName}] not present on response."
        );

        if (!$cookie || null === $value) {
            return $this;
        }

        $cookieValue = $cookie->getValue();

        PHPUnit::assertEquals(
            $value, $cookieValue,
            "Cookie [{$cookieName}] was found, but value [{$cookieValue}] does not match [{$value}]."
        );

        return $this;
    }

    /**
     * Asserts that the response contains the given cookie and is expired.
     *
     * @param string $cookieName
     * @return $this
     */
    public function assertCookieExpired($cookieName): self
    {
        PHPUnit::assertNotNull(
            $cookie = $this->getCookie($cookieName),
            "Cookie [{$cookieName}] not present on response."
        );

        $expiresAt = $cookie->getExpiresTime();

        PHPUnit::assertTrue(
            $expiresAt < time(),
            "Cookie [{$cookieName}] is not expired, it expires at [{$expiresAt}]."
        );

        return $this;
    }

    /**
     * Asserts that the response contains the given cookie and is not expired.
     *
     * @param string $cookieName
     * @return $this
     */
    public function assertCookieNotExpired($cookieName): self
    {
        PHPUnit::assertNotNull(
            $cookie = $this->getCookie($cookieName),
            "Cookie [{$cookieName}] not present on response."
        );

        $expiresAt = $cookie->getExpiresTime();

        PHPUnit::assertTrue(
            $expiresAt > time(),
            "Cookie [{$cookieName}] is expired, it expired at [{$expiresAt}]."
        );

        return $this;
    }

    /**
     * Asserts that the response does not contains the given cookie.
     *
     * @param string $cookieName
     * @return $this
     */
    public function assertCookieMissing($cookieName): self
    {
        PHPUnit::assertNull(
            $this->getCookie($cookieName),
            "Cookie [{$cookieName}] is present on response."
        );

        return $this;
    }

    /**
     * Get the given cookie from the response.
     *
     * @param string $cookieName
     * @return \Symfony\Component\HttpFoundation\Cookie|null
     */
    protected function getCookie($cookieName): ?\Symfony\Component\HttpFoundation\Cookie
    {
        foreach ($this->response->headers->getCookies() as $cookie) {
            if ($cookie->getName() === $cookieName) {
                return $cookie;
            }
        }

        return null;
    }

    /**
     * Assert that the given string is contained within the response.
     *
     * @param string $value
     * @param bool $escaped
     * @return $this
     */
    public function assertSee($value, $escaped = true): self
    {
        $value = $escaped ? htmlspecialchars($value) : $value;

        PHPUnit::assertStringContainsString((string)$value, $this->response->getContent());

        return $this;
    }

    /**
     * Assert that the given strings are contained in order within the response.
     *
     * @param array $values
     * @param bool $escaped
     * @return $this
     */
    public function assertSeeInOrder(array $values, $escaped = true): self
    {
        $values = $escaped ? array_map('htmlspecialchars', ($values)) : $values;

        PHPUnit::assertThat($values, new SeeInOrder($this->response->getContent()));

        return $this;
    }

    /**
     * Assert that the given string is contained within the response text.
     *
     * @param string $value
     * @param bool $escaped
     * @return $this
     */
    public function assertSeeText($value, $escaped = true): self
    {
        $value = $escaped ? htmlspecialchars($value) : $value;

        PHPUnit::assertStringContainsString((string)$value, strip_tags($this->response->getContent()));

        return $this;
    }

    /**
     * Assert that the given strings are contained in order within the response text.
     *
     * @param array $values
     * @param bool $escaped
     * @return $this
     */
    public function assertSeeTextInOrder(array $values, $escaped = true): self
    {
        $values = $escaped ? array_map('htmlspecialchars', ($values)) : $values;

        PHPUnit::assertThat($values, new SeeInOrder(strip_tags($this->response->getContent())));

        return $this;
    }

    /**
     * Assert that the given string is not contained within the response.
     *
     * @param string $value
     * @param bool $escaped
     * @return $this
     */
    public function assertDontSee($value, $escaped = true): self
    {
        $value = $escaped ? htmlspecialchars($value) : $value;

        PHPUnit::assertStringNotContainsString((string)$value, $this->response->getContent());

        return $this;
    }

    /**
     * Assert that the given string is not contained within the response text.
     *
     * @param string $value
     * @param bool $escaped
     * @return $this
     */
    public function assertDontSeeText($value, $escaped = true): self
    {
        $value = $escaped ? htmlspecialchars($value) : $value;

        PHPUnit::assertStringNotContainsString((string)$value, strip_tags($this->response->getContent()));

        return $this;
    }

    /**
     * Assert that the response is a superset of the given JSON.
     *
     * @param array $data
     * @param bool $strict
     * @return $this
     */
    public function assertJson(array $data, $strict = false): self
    {
        PHPUnit::assertArraySubset(
            $data, $this->decodeResponseJson(), $strict, $this->assertJsonMessage($data)
        );

        return $this;
    }

    /**
     * Get the assertion message for assertJson.
     *
     * @param array $data
     * @return string
     */
    protected function assertJsonMessage(array $data): string
    {
        $expected = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $actual = json_encode($this->decodeResponseJson(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        return 'Unable to find JSON: ' . PHP_EOL . PHP_EOL .
            "[{$expected}]" . PHP_EOL . PHP_EOL .
            'within response JSON:' . PHP_EOL . PHP_EOL .
            "[{$actual}]." . PHP_EOL . PHP_EOL;
    }

    /**
     * Assert that the expected value and type exists at the given path in the response.
     *
     * @param string $path
     * @param mixed $expect
     * @return $this
     */
    public function assertJsonPath($path, $expect): self
    {
        PHPUnit::assertSame($expect, $this->json($path));

        return $this;
    }

    /**
     * Assert that the response has the exact given JSON.
     *
     * @param array $data
     * @return $this
     */
    public function assertExactJson(array $data): self
    {
        $actual = json_encode(Arr::sortRecursive(
            (array)$this->decodeResponseJson()
        ));

        PHPUnit::assertEquals(json_encode(Arr::sortRecursive($data)), $actual);

        return $this;
    }

    /**
     * Assert that the response contains the given JSON fragment.
     *
     * @param array $data
     * @return $this
     */
    public function assertJsonFragment(array $data): self
    {
        $actual = json_encode(Arr::sortRecursive(
            (array)$this->decodeResponseJson()
        ));

        foreach (Arr::sortRecursive($data) as $key => $value) {
            $expected = $this->jsonSearchStrings($key, $value);

            PHPUnit::assertTrue(
                Str::contains($actual, $expected),
                'Unable to find JSON fragment: ' . PHP_EOL . PHP_EOL .
                '[' . json_encode([$key => $value]) . ']' . PHP_EOL . PHP_EOL .
                'within' . PHP_EOL . PHP_EOL .
                "[{$actual}]."
            );
        }

        return $this;
    }

    /**
     * Assert that the response does not contain the given JSON fragment.
     *
     * @param array $data
     * @param bool $exact
     * @return $this
     */
    public function assertJsonMissing(array $data, $exact = false): self
    {
        if ($exact) {
            return $this->assertJsonMissingExact($data);
        }

        $actual = json_encode(Arr::sortRecursive(
            (array)$this->decodeResponseJson()
        ));

        foreach (Arr::sortRecursive($data) as $key => $value) {
            $unexpected = $this->jsonSearchStrings($key, $value);

            PHPUnit::assertFalse(
                Str::contains($actual, $unexpected),
                'Found unexpected JSON fragment: ' . PHP_EOL . PHP_EOL .
                '[' . json_encode([$key => $value]) . ']' . PHP_EOL . PHP_EOL .
                'within' . PHP_EOL . PHP_EOL .
                "[{$actual}]."
            );
        }

        return $this;
    }

    /**
     * Assert that the response does not contain the exact JSON fragment.
     *
     * @param array $data
     * @return $this
     */
    public function assertJsonMissingExact(array $data): self
    {
        $actual = json_encode(Arr::sortRecursive(
            (array)$this->decodeResponseJson()
        ));

        foreach (Arr::sortRecursive($data) as $key => $value) {
            $unexpected = $this->jsonSearchStrings($key, $value);

            if (!Str::contains($actual, $unexpected)) {
                return $this;
            }
        }

        PHPUnit::fail(
            'Found unexpected JSON fragment: ' . PHP_EOL . PHP_EOL .
            '[' . json_encode($data) . ']' . PHP_EOL . PHP_EOL .
            'within' . PHP_EOL . PHP_EOL .
            "[{$actual}]."
        );

        return $this;
    }

    /**
     * Get the strings we need to search for when examining the JSON.
     *
     * @param string $key
     * @param string $value
     * @return array
     */
    protected function jsonSearchStrings($key, $value): array
    {
        $needle = substr(json_encode([$key => $value]), 1, -1);

        return [
            $needle . ']',
            $needle . '}',
            $needle . ',',
        ];
    }

    /**
     * Assert that the response has a given JSON structure.
     *
     * @param array|null $structure
     * @param array|null $responseData
     * @return $this
     */
    public function assertJsonStructure(array $structure = null, $responseData = null): self
    {
        if (null === $structure) {
            return $this->assertExactJson($this->json());
        }

        if (null === $responseData) {
            $responseData = $this->decodeResponseJson();
        }

        foreach ($structure as $key => $value) {
            if (is_array($value) && $key === '*') {
                PHPUnit::assertIsArray($responseData);

                foreach ($responseData as $responseDataItem) {
                    $this->assertJsonStructure($structure['*'], $responseDataItem);
                }
            } elseif (is_array($value)) {
                PHPUnit::assertArrayHasKey($key, $responseData);

                $this->assertJsonStructure($structure[$key], $responseData[$key]);
            } else {
                PHPUnit::assertArrayHasKey($value, $responseData);
            }
        }

        return $this;
    }

    /**
     * Assert that the response JSON has the expected count of items at the given key.
     *
     * @param int $count
     * @param string|null $key
     * @return $this
     */
    public function assertJsonCount(int $count, $key = null): self
    {
        if ($key) {
            $key = PropertyAccessorHelper::fromDotNotation($key);

            PHPUnit::assertCount(
                $count, $this->propertyAccessor->getValue($this->json(), $key),
                "Failed to assert that the response count matched the expected {$count}"
            );

            return $this;
        }

        PHPUnit::assertCount($count,
            $this->json(),
            "Failed to assert that the response count matched the expected {$count}"
        );

        return $this;
    }

    /**
     * Validate and return the decoded response JSON.
     *
     * @param string|null $key
     * @return mixed
     */
    public function decodeResponseJson($key = null)
    {
        $decodedResponse = json_decode($this->response->getContent(), true);

        if (null === $decodedResponse || $decodedResponse === false) {
            PHPUnit::fail('Invalid JSON was returned from the route.');
        }

        if (null === $key) {
            return $decodedResponse;
        }

        $key = PropertyAccessorHelper::fromDotNotation($key);

        return $this->propertyAccessor->getValue($decodedResponse, $key);
    }

    /**
     * Validate and return the decoded response JSON.
     *
     * @param string|null $key
     * @return mixed
     */
    public function json($key = null)
    {
        return $this->decodeResponseJson($key);
    }

    /**
     * Dump the content from the response.
     *
     * @return $this
     */
    public function dump(): self
    {
        $content = $this->response->getContent();

        $json = json_decode($content);

        if (json_last_error() === JSON_ERROR_NONE) {
            $content = $json;
        }

        dump($content);

        return $this;
    }

    /**
     * Dump the headers from the response.
     *
     * @return $this
     */
    public function dumpHeaders(): self
    {
        dump($this->response->getHeaders());

        return $this;
    }
}
