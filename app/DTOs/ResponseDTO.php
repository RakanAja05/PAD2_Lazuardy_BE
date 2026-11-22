<?php

namespace App\DTOs;

class ResponseDTO
{
    public string $status;
    public string $message;
    public mixed $data;
    public mixed $error;
    public int $code;

    public function __construct(
        string $status,
        string $message,
        mixed $data = null,
        mixed $error = null,
        int $code = 200
    ) {
        $this->status = $status;
        $this->message = $message;
        $this->data = $data;
        $this->error = $error;
        $this->code = $code;
    }

    /**
     * Backward compatibility: legacy controllers accessed `$dto->payload`.
     *
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        if ($name === 'payload') {
            return $this->toArray();
        }

        return null;
    }

    public function toArray(): array
    {
        $payload = [
            'status' => $this->status,
            'message' => $this->message,
        ];

        if ($this->status === 'success') {
            $payload['data'] = $this->data ?? (object) [];
        } else {
            $payload['error'] = $this->error ?? (object) [];
        }

        return $payload;
    }
}
