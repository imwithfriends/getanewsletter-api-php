<?php

namespace Gan;

use Httpful\Http;

abstract class EntityManager
{
    protected $basePath;
    protected $entityClass;
    protected $queryClass;
    protected $writableFields;
    protected $lookupField;

    public function __construct($api)
    {
        $this->api = $api;
    }

    protected function getPath($id)
    {
        return rtrim($this->basePath, '/') . '/' . rtrim($id, '/') . '/';
    }

    private function lookupPath(Entity $entity)
    {
        $lookupField = $this->lookupField;
        if (!$entity->$lookupField) {
            throw new \Exception('Missing required property: ' . $lookupField);
        }
        return $this->getPath($entity->$lookupField);
    }

    protected function constructEntity($data)
    {
        $entity = new $this->entityClass();
        foreach (array_keys(get_object_vars($entity)) as $property) {
            if (property_exists($data, $property)) {
                $entity->$property = $data->$property;
            }
        }
        return $entity;
    }

    private function normalizeEntity(Entity $contact)
    {
        $data = new \stdClass();
        foreach ($this->writableFields as $property) {
            $data->$property = $contact->$property;
        }
        return $data;
    }

    public function get($id)
    {
        $resource = $this->getPath($id);
        $response = $this->api->call(Http::GET, $resource);
        $result = $this->constructEntity($response->body);
        $result->setPersisted();
        return $result;
    }

    public function save(Entity $entity, $overwrite = false)
    {
        $data = $this->normalizeEntity($entity);

        if ($overwrite) {
            $response = $this->api->call(Http::PUT, $this->lookupPath($entity), $data);
        } else {
            if ($entity->isPersisted()) {
                $response = $this->api->call(Http::PATCH, $this->lookupPath($entity), $data);
            } else {
                $uri =  rtrim($this->basePath, '/') . '/';
                $response = $this->api->call(Http::POST, $uri, $data);
            }
        }

        $result = $this->constructEntity($response->body);
        $result->setPersisted();
        return $result;
    }

    public function overwrite(Entity $entity)
    {
        return $this->save($entity, true);
    }

    public function query($filters)
    {
        $uri = rtrim($this->basePath, '/') . '/?' . http_build_query($filters);
        $response = $this->api->call(Http::GET, $uri);

        $result = [];
        foreach ($response->body->results as $data) {
            $result[] = $this->constructEntity($data);
        }
        return $result;
    }

    public function delete(Entity $entity)
    {
        $this->api->call(Http::DELETE, $this->lookupPath($entity));
    }
}
