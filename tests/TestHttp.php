<?php

namespace Tests;

use Illuminate\Testing\TestResponse;

trait TestHttp
{
    /**
     * URL that will be used for json request.
     */
    protected string $url = '';

    /**
     * Set response GET for controller test index.
     */
    public function jsonGet(): TestResponse
    {
        return $this->getJson($this->url);
    }

    /**
     * Set response POST for controller test store.
     */
    public function jsonPost(array $data = []): TestResponse
    {
        return $this->postJson($this->url, $data);
    }

    /**
     * Set response PUT for controller test update.
     */
    public function jsonPut(array $form): TestResponse
    {
        return $this->putJson($this->url, $form);
    }

    /**
     * Set response DELETE for controller test destroy.
     */
    public function jsonDelete(): TestResponse
    {
        return $this->deleteJson($this->url);
    }

    /**
     * Call GET request and assert the json structure.
     *
     * @param \Closure(\Illuminate\Testing\Fluent\AssertableJson): (\Illuminate\Testing\Fluent\AssertableJson) $callback
     */
    public function assertJsonGet(\Closure $callback): TestResponse
    {
        return $this->jsonGet()
            ->assertOk()
            ->assertJson($callback);
    }

    /**
     * Call POST request and assert the json structure.
     *
     * @param array<string,mixed> $data
     * @param \Closure(\Illuminate\Testing\Fluent\AssertableJson): (\Illuminate\Testing\Fluent\AssertableJson) $callback
     */
    public function assertJsonPost(array $data, \Closure $callback): TestResponse
    {
        return $this->jsonPost($data)
            ->assertSuccessful()
            ->assertJson($callback);
    }

    /**
     * Call PUT request and assert the json structure.
     *
     * @param array<string,mixed> $data
     * @param \Closure(\Illuminate\Testing\Fluent\AssertableJson): (\Illuminate\Testing\Fluent\AssertableJson) $callback
     */
    public function assertJsonPut(array $data, \Closure $callback): TestResponse
    {
        return $this->jsonPut($data)
            ->assertSuccessful()
            ->assertJson($callback);
    }

    /**
     * Call DELETE request and assert the json structure.
     *
     * @param \Closure(\Illuminate\Testing\Fluent\AssertableJson): (\Illuminate\Testing\Fluent\AssertableJson) $callback
     */
    public function assertJsonDelete(\Closure $callback): TestResponse
    {
        return $this->jsonDelete()
            ->assertSuccessful()
            ->assertJson($callback);
    }

    /**
     * Call POST request and assert the json response has error message.
     *
     * @param array<string,mixed> $data
     * @param string|array $errors
     */
    public function assertJsonPostErrors(array $data, $errors): TestResponse
    {
        return $this->jsonPost($data)
            ->assertUnprocessable()
            ->assertJsonValidationErrors($errors);
    }

    /**
     * Call PUT request and assert the json response has error message.
     *
     * @param array<string,mixed> $data
     * @param string|array $errors
     */
    public function assertJsonPutErrors(array $data, $errors): TestResponse
    {
        return $this->jsonPut($data)
            ->assertUnprocessable()
            ->assertJsonValidationErrors($errors);
    }

    /**
     * Call DELETE request and assert the json response has error message.
     *
     * @param string|array $errors
     */
    public function assertJsonDeleteErrors($errors): TestResponse
    {
        return $this->jsonDelete()
            ->assertUnprocessable()
            ->assertJsonValidationErrors($errors);
    }
}
