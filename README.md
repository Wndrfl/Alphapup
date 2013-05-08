# ALPHAPUP

A PHP based framework by WNDRFL.

## Boot Process

- user defines plugins in Environment\Alphapup
- user defines namespaces in Environment\Bootstrap
- web/index.php makes an instance of Alphapup which extends a Kernel
	- the kernel goes to each registered plugin and creates a basic config for each, which basically sets up the directory path for the plugin
	- the kernel builds a container based on these initial configs
		- the kernel goes to each registered plugin and runs a boot() process on each, passing the Container to the process
			- the plugins each have an opportunity to inject their configuration into the Container
		- the kernel goes to each registered plugin and runs a postBoot() process, allowing the plugin to interact with the framework AFTER everything has been configged
- the kernel fires events for requests and responses
- the kernel gets the router and passes it the request
- the kernel gets the dispatcher and dispatches the request to the respnse
- the kernel renders the response
- the kernel caches the config
- the kernel shutsdown, which tells the profiler to save

## Components

ALPHAPUP comes equipped with a set of tools called Components. These tools are easily replaceable and upgradeable - and operate outside of the foundation of the framework - but, they are relied upon to provide much of the rich functionality built into the platform.

### The Debug Component

The Debug Component provides a rich set of tools to help the development of an application.

#### Error Handler

The Error Handler is utilized by the Kernel to handle the occurrence of any errors during page load. It logs errors according to their severity, and keeps them from being visible to the user unless in the correct environment (such as a development environment). 

#### Exception Handler

The Exception Handler is another tool utilized by the Kernel to handle the occurrence of any Uncaught Exceptions. When one occurs, the handler will display a useful error screen to help debug the situation.

#### Profiler

The Profiler is a tool utilized throughout the framework to record various data about what happened internally during a page load. This comes in handy when trying to understand things like what database queries were executed, which events were fired, or which exceptions were thrown.

The Profiler works by generating a "Profile" for each page load. The Profile itself is an empty shell that can be filled with different data about the load. This data comes in the form of a DataCollector, which is an object that another service can create, fill with data, and pass to the Profiler for storing.

The DataCollector operates by serving two roles: it "collects" data, and it provides a unique name for its set of data. When using a DataCollector, a service should create its own version of a DataCollector that implements the DataCollectorInterface.

### The View Component

The View Component manages the creation of a response that should appear on the screen. It is used primarily by a Controller to create visual representations of data.

The View Component operates with 4 basic steps:

**1. Load with data -** A Controller can pass data to / access data in a View instance by simply using the View's public variables (no getters or setters needed).

**2. Choose a theme -** A "theme" is a common wrapper that different views might share. For example, an eCommerce site's checkout process section might have pages that all share a visual theme that is different than the normal theme of the site. The View accommodates for this by providing a `theme()` function to define the location of your desired theme.

**3. Add view(s) -** In this case, "views" refers to the actual template files that will display the data loaded into the View Component. As it is common for different pages to share the same template parts, this method can be called multiple times to access files in different areas.

**4. Display -** When the creation of the View is complete, the `display()` method will output the version of the View that was created. On its own, this method would immediately display the response to the screen, however, on a normal load, ALPHAPUP's `Response` service (not related to the View Component itself) would monitor for any generated output, and delay the display until the last second.

### The Introspect Component

The Introspect Component gives ALPHAPUP a set of tools to understand the code that goes into a class.

For example, there may be times when configuration of an object is stored in specially formatted comments. The Introspect Component can help in this situation by allowing the framework to read and parse the comments of a class.

### The Dexter Component

The Dexter Component is ALPHAPUP's take on a DBAL (Database Abstraction Layer).

The idea is that a framework should connect to a database at only a single point - which makes altering that database much easier if need be. In this way, Dexter acts as a layer in between the rest of ALPHAPUP and the desired database.

Dexter does not attempt to build queries for you. Instead it attempts to take a statement, execute it, and return results in the most efficient manner - utilizing a cache if necessary.

Results are returned in an easily traversable object.

### The Carto Component

The Carto Component is ALPHAPUP's take on an ORM (Object Relational Mapper).

The role of Carto in ALPHAPUP is to serve as an additional layer of abstraction between the rest of the framework and the database. In this case, it is actually a layer in between ALPHAPUP and Dexter (the DBAL). The concept here, is that ALPHAPUP should be as separated from the architecture of the database as possible.

To accurately visualize what Carto provides ALPHAPUP, think of a situation where ALPHAPUP doesn't even know if a database exists. All it knows is that it can ask Carto for information, and Carto will return it. Whether or not Carto speaks directly to a database, or uses a DBAL such as Dexter, or even makes up the information on its own, the framework doesn't know or care. This is called a Repository Pattern (Carto is a Repository full of magical information).

To create this separation, Carto employs two main notions of an `Entity` and a `Repository`.

An `Entity` represents (in an abstract sense) the contents of a single row in a database. For example, in a table called "Accounts", there could be an `Entity` for each individual row which holds an email and a password. In this way, it's almost easier to view an `Entity` as a representation of something real...almost tangible.

A `Repository`, as explained above, is a black box that can be communicated with to retrieve things like `Entities`. For example, if I wanted information about a specific account, I could go to my `AccountsRepository` as ask it for the `Entity` that corresponds to the Account with `id` of x. I don't necessarily care where the `AccountsRepository` gets this `Entity` from, because as long as it is correct, I can go about my business.

The concept of the `Repository` therefore allows the entire framework to disengage from a datasource and simply trust that whatever information it receives from Carto will be correct.

The mission of Carto, therefore, is to provide this level of abstraction - and to do it with accurate information.

#### Carto Entities

An `Entity` in Carto is an object that aims to represent a singular concept. As in the real world, however, concepts are commonly related to other concepts. To mimic this, Carto presents a method of configuring an `Entity` to be 'related' to others.

The method of defining this relationship is through special comments, and it takes place in the `Entity` itself.

- **Entity level configurations**
	- **Table definition**
		- `@Carto\Table (name="tableName")`
		- This configuration is placed before the declaration of the class itself, and serves to define the database table that is used to store this `Entity`

- **Parameter configurations**
	- **ID Parameter**
		- `@Carto\Id (generation="auto")`
		- This configuration informs Carto that this parameter is to be treated as the primary unique identifier for the `Entity`.

- **Parameter types (can only have one type)**
	- **Basic Column Parameter**
		- `@Carto\Column (name="columnName")`
		- This parameter is tied directly to a single column in the database table, and will be filled with the value of the supplied `columnName`.

	- **One to One Relationship (dominant side)**
		- `@Carto\OneToOne (entity="AnotherEntity",mappedBy="parameterInAnotherEntity",lazy=true)`
		- This parameter holds a 'one to one' relationship with another `Entity` type.
		- The `mappedBy` argument defines the parameter of the *other* `Entity` that will hold the `Entity` that is currently being defined.
		- The `lazy` argument defines whether or not the related `Entity` should be loaded immediately, or only when used. If there is a chance that the relation will not be needed on a page load, then enabling lazy loading will help speed up the query to load the `Entities`. However, if the related `Entity` will almost certainly be used, disabling this argument will keep Carto from doing two queries, when one would suffice.

	- **One to One Relationship (passive side)**
		- `@Carto\OneToOne (entity="AnotherEntity",local="localMappingColumn",foreign="foreignMappingColumn",inversedBy="parameterInAnotherEntity",lazy=true)`
		- The `local` argument defines the table column where this `Entity` stores the `id` of the other `Entity`.
		- The `foreign` argument defines the column in the other `Entity` that holds the value to be found in the `local` column.
		- The `inversedBy` argument defines the parameter in the other `Entity` that is used to hold the current `Entity`

	- **One to Many Relationship**
		- `@Carto\OneToMany (entity="AnotherEntity",mappedBy="parameterInAnotherEntity",lazy=true)`
		- This parameter is meant to hold an `array` of related `Entities`.
		- The `Entities` in the parameter will be stored as an `array`
	
	- **Many to One Relationship**
		- `@Carto\ManyToOne (entity="AnotherEntity",local="localMappingColumn",foreign="foreignMappingColumn",inversedBy="parameterInAnotherEntity",lazy=true)`
		- This parameter is meant to hold a relationship to another `Entity` where this type of `Entity` can only have one relationship with the other, but the other can have many relationship with this type.
		- The `local` argument defines the table column where this `Entity` stores the `id` of the other `Entity`.
		- The `foreign` argument defines the column in the other `Entity` that holds the value to be found in the `local` column.
		- The `inversedBy` argument defines the parameter in the other `Entity` that is used to hold the current `Entity`
		
	- **Many to Many Relationship (dominant side)**
		- `@Carto\ManyToMany (entity="AnotherEntity",mappedBy="parameterInAnotherEntity",lazy=true)`
		- This configuration tells Carto that this parameter is meant to hold an `array` of related `Entities`.
		- The `mappedBy` argument defined the parameter of the *other* `Entity` that will also hold an `array` of `Entities` of the type that is currently being defined.
		
	- **Many to Many Relationship (passive side)**
		- `@Carto\ManyToMany (entity="AnotherEntity", inversedBy="parameterInAnotherEntity", local="localMappingColumn", foreign="foreignMappingColumn", joinTable="joinTableName", joinColumnLocal="joinTableColumnForLocal", joinColumnForeign="joinTableColumnForForeign")`
		- This configuration tells Carto that this parameter is meant to hold an `array` of related `Entities`.
		- The `inversedBy` argument defined the parameter of the *other* `Entity` that will also hold an `array` of `Entities` of the type that is currently being defined.
		- the `local` argument defines the local column that will be matched in the join table.
		- the `foreign` argument defines the foreign column (for the other `Entity`) that will be matched in the join table.
		- the `joinTable` argument defines the database table that holds the relationships between these two types of `Entities`.
		- the `joinColumnLocal` argument defines the column in the join table that holds the relationship to the local table.
		- the `joinColumnForeign` argument defines the column in the join table that holds the relationship to the foreign table.

#### Carto Query Language (CQL)

To say it again, Carto's goal is to completely separate the framework from a concept of a database, and instead provide it with the concept of a repository. However, the framework still needs a language to communicate with the repository to ask it for what it needs. For this reason, Carto comes with its own query language called CQL.

CQL is similar in syntax to MySQL, but it ****never** references the database. Instead, the language refers to `Entities`.

Here is an example CQL statement:

<pre>
	FETCH a, au FROM Account a ASSOCIATED a._accountUser au OPTIONAL ASSOCIATED a._comments c WHERE a._id = 1 AND c._id = 1 LIMIT 10
</pre>