<?php

namespace Gan;

use Httpful\Http;

/**
 * Base entity manager.
 *
 * The entity manager is following the Data Mapper pattern and is responsible
 * for the mapping of the business objects (Contact, List, etc.) to the REST API.
 * by supporting the basic CRUD operations. As an abstract class it need to be
 * extended and it's configuration variables set up.
 */
abstract class EntityManager
{
    /**
     * The base path of the model, i.e. the API endpoint (e.g. 'contacts', 'lists').
     * @var string
     */
    protected $basePath;

    /**
     * The class of the entity (e.g. 'Gan\Contact').
     * @var mixed
     */
    protected $entityClass;

    /**
     * Array listing all writable fields. It is required by the default normalizeEntity()
     * method that is used to clean up the entity before sending it to the API.
     * @var array
     */
    protected $writableFields;

    /**
     * The name of the field used for model lookup.
     * @var string
     */
    protected $lookupField;

    /**
     * Constructor.
     * @param \Gan\Api $api The Api instance to make all operations against.
     */
    public function __construct($api)
    {
        $this->api = $api;
    }

    /**
     * Method used by the entity manager to construct the resource path.
     *
     * This method would simply concatenate the lookup id at the end of
     * the base path: presource/<id>/. You may have to override it if you
     * have an unusual resource path scheme (for example the the subscribers'
     * endpoint: lists/<hash>/subscribers/<email>/).
     *
     * @param mixed $id The id.
     * @return string
     */
    protected function getPath($id)
    {
        return rtrim($this->basePath, '/') . '/' . rtrim($id, '/') . '/';
    }

    /**
     * Method used by the entity manager to construct a resource path from an entity.
     *
     * It'll read the content of the lookupField variable to determine which field
     * have to be used to construct the path. Again, you may want to override this
     * method if you need to construct a path different than resource/<id>/.
     *
     * @param \Gan\Entity $entity The entity to get the path for.
     * @return string The resource path.
     * @throws \Exception if the lookup field is empty.
     */
    protected function lookupPath($entity)
    {
        $lookupField = $this->lookupField;
        if (!$entity->$lookupField) {
            throw new \Exception('Missing required property: ' . $lookupField);
        }
        return $this->getPath($entity->$lookupField);
    }

    /**
     * Method used by the entity manager to construct an entity from the API data.
     *
     * Given transfer data object (stdClass instance) it have to build and initialize the
     * corresponding entity. The default behaviour is to call the entity constructor
     * without arguments and fill all entity fields with the supplied data.
     * Override this method if your entities require more complex construction procedure.
     *
     * @param \stdClass $data The transfer data object from the API.
     * @return \Gan\entityClass The constructed entity.
     */
    public function constructEntity($data)
    {
        $entity = new $this->entityClass();
        foreach (array_keys(get_object_vars($entity)) as $property) {
            if (property_exists($data, $property)) {
                $entity->$property = $data->$property;
            }
        }
        return $entity;
    }

    /**
     * Used to generate a payload for the write operations to the API from an entity.
     *
     * This method will use the contents of the writableFields array to get
     * only the writable fields from the entity and pack them in an data transfer object
     * that will be sent to the API on save() or overwrite().
     * Override this method if required.
     *
     * @param \Gan\Entity $entity The entity to extract data from.
     * @return \stdClass The transfer data object.
     */
    public function normalizeEntity($entity)
    {
        $data = new \stdClass();
        foreach ($this->writableFields as $property) {
            if (property_exists($entity, $property)) {
                $data->$property = $entity->$property;
            }
        }
        return $data;
    }

    /**
     * Retreives a single entity from the API.
     *
     * @param string|int $id The identificator of the entity (e.g. the email of a contact).
     * @return mixed The entity object.
     * @throws Gan\ApiException on failure (e.g. the entity is not found).
     */
    public function get($id)
    {
        $resource = $this->getPath($id);
        $response = $this->api->call(Http::GET, $resource);
        $result = $this->constructEntity($response->body);
        $result->setPersisted();
        return $result;
    }

    /**
     * Saves or updates an entity.
     *
     * If the $overwrite flag is set, it will make a PUT request to the API.
     * It will return the updated/created entity as result.
     *
     * @param \Gan\Entity $entity The entity object to update/save.
     * @param boolean $overwrite Set to true if you want
     * @return \Gan\Entity The updated/created entity.
     * @throws \Gan\ApiException if there is an error from the API.
     * @throws \Exception if the normalization fails, i.e. missing lookup field.
     */
    public function save($entity, $overwrite = false)
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

    /**
     * Overwrites an entity.
     *
     * It internally calls save() with the $overwrite flag set.
     *
     * @param \Gan\Entity $entity
     * @return \Gan\Entity The created entity.
     * @throws \Gan\ApiException if there is an error from the API.
     * @throws \Exception if the normalization fails, i.e. missing lookup field.
     */
    public function overwrite($entity)
    {
        return $this->save($entity, true);
    }

    /**
     * Low level method for making search queries.
     *
     * Used to get collections of entities based on a search query.
     * The query parameters are specific to the entity type, except
     * page and paginate_by which are common.
     *
     * @param array $filters Associative array of query parameters (e.g. ['search_email'=>'test@', 'page'=>2])
     * @return array The result array of entities.
     * @throws \Gan\ApiException if there is an error from the API.
     */
    public function query($filters = [])
    {
        $uri = rtrim($this->basePath, '/') . '/?' . http_build_query($filters);
        $response = $this->api->call(Http::GET, $uri);

        $result = [];
        foreach ($response->body->results as $data) {
            $result[] = $this->constructEntity($data)->setPersisted();
        }
        return $result;
    }

    /**
     * Deletes an entity.
     *
     * @param \Gan\Entity $entity The entity to delete.
     * @throws \Gan\ApiException if there is an error from the API.
     */
    public function delete($entity)
    {
        $this->api->call(Http::DELETE, $this->lookupPath($entity));
    }
}
