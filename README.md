FLOW3 Semantic Framework
========================

The FLOW3 Semantic Framework helps FLOW3 developers to participate in the
Semantic Web, giving them tools to adhere to best-practices.

It has been developed by Sebastian Kurfuerst in his Diploma Thesis, and is
continued by him furthermore.

WARNING
-------

The current software is still in an unstable state; as it has been developed
for a FLOW3 Beta Version. It is currently being updated to the final version.

Functionality in a Nutshell
---------------------------

- Universal Mapping from Domain Models to RDF. We call this *Schema Mapping*
- Export Domain Model data as RDF (under a persistent URI) (not yet migrated to latest FLOW3)
- Send Domain Model data as RDF to a Triple Store (not yet migrated to latest FLOW3)
- transparently add RDFa to templates (not yet migrated to latest FLOW3)
- Enrich Domain Model data with Linked Data URIs, including longer texts (experimental)
- Export legacy databases to RDF, similar to Triplify

Exporting Legacy Databases
==========================

This feature has been inspired by Triplify (http://triplify.org/). However,
because of coding style issues, it has been re-implemented.

To export a legacy database, you need two things. A *Driver* which has to
be implemented once for each application, and a *Configuration* snippet
which configures the driver.

Let's assume you want to use the *Redmine* driver which is already part of this package.
In this case, just insert the following configuration inside your Settings.yaml file:

```yaml
SandstormMedia:
  Semantic:
    triplify:

      # For each legacy database you want to export, you need such a section
	  # below.
      localRedmine:
        driver: 'SandstormMedia\Semantic\Triplify\Driver\Redmine'
        pdoConnection: 'mysql:host=127.0.0.1;dbname=redmine' # Adjust host and DB name
        pdoUser: 'redmine'                                   # Adjust DB username
        pdoPassword: '...'                                   # Adjust DB password
        baseUri: 'http://forge.typo3.org/'                   # Adjust base URI to your redmine instance

```

That's it already! Now, you can run:

```
./flow3 triplify:generatetriples localRedmine #localRedmine is the identifier from the Settings.yaml file above
```

... and you get a list of triples which have been exported from the database.

Redmine Driver
--------------

The Redmine Driver currently exports issues and projects, and the links between them.

Writing your own Driver
-----------------------

Writing your own driver is easy: Just subclass `\SandstormMedia\Semantic\Triplify\AbstractDriver`
and do the following:

1.  define the objects you want to export inside the $objects array. The key is an internal identifier,
	where the value is the URI pattern which generates the object's identity:

	```php
	protected $objects = array(
	   'project' => '{BASEURI}/projects/{_id}',
	   'issue' => '{BASEURI}/issues/{_id}'
	);
	```

	Here, `{BASEURI}` is replaced with the base URI configured in the YAML config,
	and `{_id}` is a special property which is the internal object ID.

2.  For each object, set the object's type using the `$<object>Type` variable:

	```php
	protected $projectType = 'doap:Project';
	protected $issueType = 'dbug:Issue';
	```

3.  Now comes the cool part: You now need to define SQL queries which extract the
	wanted information from the database. For each object, multiple queries
	can be defined in an array named `$<object>Queries`.

	You need to alias the column names to the following:

	* `_id` for the ID property which is used to build the persistent URI:

		```sql
		SELECT
			p.identifier AS _id
		FROM projects p
		```

	* an *rdf predicate* when you want to map a column to a simple RDF *literal*:

		```sql
		SELECT
			i.subject AS 'rdfs:label',
			i.description AS 'rdfs:comment'
		FROM issues i
		```

	* an *rdf predicate followed by an object type* for relations to other objects.

		The example shows the relation from projects to the `issue`.

		```sql
		SELECT p.identifier AS _id,
		       i.id AS 'dbug:issue->issue'
		FROM issues i, projects p WHERE p.id = i.project_id"
		```

	* an *rdf predicate followed by a callback function* for custom post-processing.

		This is the most flexible type, where you can for example covert dates
		to the corresponding RDF Literal:

		```sql
		SELECT
			p.updated_on AS 'dcterms:modified->asDateTime()
		FROM projects p
		```

		The callback function ``asDateTime`` gets the column value as string parameter,
		and must return a subclass of `\SandstormMedia\Semantic\Core\Rdf\Concept\RdfNode`.

		As an example, the aforementioned ``asDateTime`` function looks as follows:

		```php
		protected function asDateTime($value) {
			$dateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $value);
			return new Literal($dateTime);
		}
		```

NOTE: Make sure all the prefixes you use (like ``dbug`` or ``doap``) are registered
in `Settings.yaml` `SandstormMedia: Semantic: prefixes`.

Further Reading
===============

- Diploma Thesis (TODO)