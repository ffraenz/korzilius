# Korzilius Backbone

The korzilius backbone service provides an interface to access and manipulate resources stored and managed by external third party applications in foreign database structures.

## Resources

### Document

Documents are stored and managed using a third party Windows application called ELOprofessional 2011 v8.00.060.

#### GET /documents

Gets documents matching the given filters. Documents are ordered by update time in descending order (last updated document appears first).

##### Parameters

- `updated_since` – unix timestamp, shows documents that have been created or updated after this timestamp
- `count` – int, limits the number of entities in result
- `offset` – int, skips a number of entities in result

##### Response

Array of document objects.

#### GET /documents/:id

Gets single document object by id or `null` if it could not be found.

##### Parameters

- `:id` – int, document id

##### Response

Json structure of a single document object:

```json
{
  "id": 29718,
  "objectId": 42052,
  "title": "Short document title",
  "description": "Complete document title",
  "path": "UPR00007/00007416.tif",
  "extension": "tif",
  "size": 4357909,
  "hash": "C59762BD3BA0ADB498A40BE34EA16C67",
  "edits": 2,
  "uploadUserId": 12,
  "uploadTime": 1477653232,
  "updateUserId": 4,
  "updateTime": 1478098014,
  "mask": {
    "id": 15,
    "label": "15-Mask name",
    "fields": [
      {
        "name": "field_name",
        "label": "Field label",
        "value": "Hello World"
      }
    ]
  }
}
```

### Client

Clients are managed by a custom made Windows application and stored in a MS SQL database.

#### GET /clients

Gets clients matching the given filters. Clients are ordered by update time in descending order (last updated client appears first).

##### Parameters

- `updated_since` – unix timestamp, shows clients that have been created or updated after this timestamp
- `count` – int, limits the number of entities in result
- `offset` – int, skips a number of entities in result

##### Response

Array of client objects.

#### GET /clients/:id

Gets single client object by id or `null` if it could not be found.

### Contract

#### GET /clients/:id/contracts

#### GET /contracts/:id



