# Graphette

## About

This Nette extension integration is providing a convenient way to define and resolve a fully featured GraphQL API.

Highly abstract and performant architecture is based on a [webonyx/graphql-php](https://webonyx.github.io/) package, which provides a solid core of this system and fully integrates into Nette Framework, allowing to use all of its features.

## Pre-requisites

Before diving into this extension, you should be familiar with GraphQL itself. If you are not, please visit [official GraphQL website](https://graphql.org/) and read some of the articles there.

## Philosophy

The core philosophy of this extension is to provide the most native integration of GraphQL into Nette framework and PHP itself.

Since the whole GraphQL API is essentially a collection of types, it is natural to define them in PHP as well. This extension provides a simple way to define GraphQL types in PHP, which are then compiled into a GraphQL schema. This schema is then used to resolve incoming GraphQL queries.

 > Minimum effort, maximum satisfaction
 >
 > *Gino D'Acampo*

## Installation

TODO

## Basic Usage

### Defining types

GraphQL types are defined as PHP classes. Each type is essentially represented by a class (there are necessary exception with Interface & Union types, but about that later), which is appropriately labelled by annotation attributes.

Let's start with a most common type - `ObjectType`. This type is used to represent a single entity in your API. For example, if you are building a blog, you would probably have a `Post` type, which would represent a single blog post.

```php
namespace App\GraphQL\Posts;

use Graphette\Graphette\Attribute as GQL;

#[GQL\ObjectType]
class Post
{
    #[GQL\Field]
    public int $id;

    #[GQL\Field]
    public string $title;

    #[GQL\Field]
    public string $content;
}
```

As you can see, the `ObjectType` is defined by a `#[GQL\ObjectType]` attribute. This attribute is used to mark the class as a GraphQL ObjectType. Name of the type is automatically determined by the name of the class itself and its namespace.

The fields of this type are defined by `#[GQL\Field]` attribute. This attribute is used to mark the property as a GraphQL field. The name of the field is the name of the property itself. The type of the field is determined by the type of the property. In this case, the `id` field is of type `Int`, `title` and `content` fields are of type `String`.

### Where is the root?

In GraphQL, there is a concept of a root type. This type is used to define the entry points of your API. There is a root type for queries and root type for mutations. Naming a class either `Queries` or `Mutations` will automatically create a root field for that type.

Let's say we have a `Queries` class defined like this:

```php
namespace App\GraphQL\Posts;

use Graphette\Graphette\Attribute as GQL;

#[GQL\ObjectType]
class Queries
{
    #[GQL\Field]
    public Post $post;

}
```

Assuming there is a rootNamespace configured as `App\GraphQL`, this will automatically create a root query field called `postsQueries` in a `Query` ObjectType. This way you can easily define and also structure entry points of you API.

### Resolving fields

These classes now represent a GraphQL schema, but they are not yet resolved. This means that if you try to execute a query, it will fail, because there is no way to resolve the fields.

If you want to resolve any field of any type, you need to define a resolver. Which is a simple two-step process.

First, you need to define a resolver class. This class is a simple Nette service which extends `Graphette\Graphette\Resolving\Resolver` class. This class is used to define a resolver for a specific type.

For a given field it resolves it has to have appropriate method defined. The method name is determined by the name of the field. Eg. if you want to resolve field post in Queries class, you need to define a method called `resolvePost`. This method has to have a first argument, which is an instance of the type you are resolving. In this case, it would be an instance of `Queries` class.

Any other arguments of the method are automatically recognized as arguments of the field. For example, if you want to resolve a field `post` with an argument `id`, you need to define a method `resolvePost` with two arguments - first one being an instance of `Queries` class and second one being an `int` argument called `id`.

```php
namespace App\GraphQL\Posts;

use Graphette\Graphette\Attribute as GQL;

class QueriesResolver extends Graphette\Graphette\Resolving\Resolver
{
    public function resolvePost(Queries $queries, int $id): void
    {
        // here you can resolve the post field in $queries object and use $id argument to find the post
        // resolving the field means setting the value of the field in the $queries object
        $post = new Post();
        // ...

        $queries->post = $post;
    }

}

```

This class of course has to be registered as a Nette service. Easiest way to do that automatically is to use Nette search extension and configure it something like this:

```yaml
search:
  resolvers:
    in: %appDir%/Schema
    extends:
      - Graphette\Graphette\Resolving\Resolver
```

Second step is to register the resolver in the type itself by defining a `resolver` attribute for the `ObjectType` annotation attribute.

```php
namespace App\GraphQL\Posts;

use Graphette\Graphette\Attribute as GQL;

#[GQL\ObjectType(resolver: QueriesResolver::class)]
class Queries
{
    #[GQL\Field]
    public Post $post;

}
```

This way, the resolver is registered for the `Queries` type and it will be used to resolve any field of this type.


### Objects as arguments

For a better reusability and convenience, you can use objects as arguments. Same as the `#[GQL\ObjectType]` you can define a class with `#[GQL\InputObjectType]` attribute. This class can than be  used as an argument for a field (resolver method).

```php
namespace App\GraphQL\Posts;

use Graphette\Graphette\Attribute as GQL;

#[GQL\InputObjectType]
class PostInput
{
    #[GQL\Field]
    public string $title;

    #[GQL\Field]
    public string $content;
}
```

This class can then be used as an argument for a field. For example, if you want to create a new post, you can define a mutation like this...

First of course you have to  have   appropriate  root      type  defined.  In this case, it would be a `Mutations` class:

```php
namespace App\GraphQL\Posts;

use Graphette\Graphette\Attribute as GQL;

#[GQL\ObjectType(resolver: MutationsResolver::class)]
class Mutations
{
    #[GQL\Field]
    public Post $createPost;

}
```

Then you have to define a resolver for this field, where you can use the `PostInput` class as an argument:

```php
namespace App\GraphQL\Posts;

use Graphette\Graphette\Attribute as GQL;

class MutationsResolver extends Graphette\Graphette\Resolving\Resolver
{
    public function resolveCreatePost(Mutations $mutations, PostInput $input): void
    {
        // here you can resolve the createPost field in $mutations object and use $input argument to create the post
        // resolving the field means setting the value of the field in the $mutations object
        $post = new Post();
        // ...

        $mutations->createPost = $post;
    }

}
```

> This is also by   many  people recognized as a general best practice, to define input arguments as `InputObjectType`. This way you can easily reuse them in multiple places and you can also easily add new fields to them without breaking anything.
>
> This extension is by any means not forcing you to use this approach, but it is recommended to bear this in mind.

## Using more complex types (abstractions & unions)

GraphQL allows you to define abstraction through interface types. And also for some general convinience union types.

### Interface types

As is the case with other types, this extension tries to make defining interface types as easy as possible. You can define an interface type by using `#[GQL\InterfaceType]` attribute.

Unfortunately, PHP does not support having  properties in interfaces. Therefore, you have to define a trait which implements those properties and attach it to the interface.

```php
namespace App\GraphQL\Posts;

use Graphette\Graphette\Attribute as GQL;
use App\GraphQL\Posts\CommentedTrait;

#[GQL\InterfaceType(
    attachedTrait: CommentedTrait::class,
)]
interface Commented
{
}
```

Than you of course have to define the trait itself:

```php
namespace App\GraphQL\Posts;

use Graphette\Graphette\Attribute as GQL;

trait CommentedTrait
{
    #[GQL\Field]
    public string $comment;

}
```

And then you can use this interface in any other type:

```php
namespace App\GraphQL\Posts;

use Graphette\Graphette\Attribute as GQL;

#[GQL\ObjectType]
class Post implements Commented
{
    use CommentedTrait;

    #[GQL\Field]
    public string $title;

    #[GQL\Field]
    public string $content;

}
```

Built-in logic alerts you with an exception on compile time, in case you forget to use the attached trait.

### Union types

> **IMPORTANT!** As stated in this [GraphQL Spec issue](https://github.com/graphql/graphql-spec/issues/215) there is currently no suport for scalar union types in GraphQL. Therefore, this extension does not support them either.

Union types are a bit more complex. You can define a union type by using `#[GQL\UnionType]` attribute. This attribute has to have a `types` argument, which is an array of types which are part of the union.

```php
namespace App\GraphQL\Posts;

use Graphette\Graphette\Attribute as GQL;

#[GQL\UnionType(
    types: [Post::class, Comment::class],
)]
class SearchResult
{
}
```

This way you can define a reusable union type. For example, if you want to search for posts and comments, you can define a field like this:

```php
namespace App\GraphQL\Posts;

use Graphette\Graphette\Attribute as GQL;

#[GQL\ObjectType]
class Queries
{
    #[GQL\Field]
    #[Unions(SearchResult::class)]
    public Post|Comment $search;

}
```

This is not the most convenient way to define a union type, but it ensures that you can reuse the union type in multiple places with a same name.

Built-in logic alerts you with an exception on compile time, in case you forget to change the set of types on the field.

## Enum types

Enum types are also supported. You can define an enum type by using `#[GQL\EnumType]` attribute on php native enum type.

```php
namespace App\GraphQL\Posts;

use Graphette\Graphette\Attribute as GQL;

#[GQL\EnumType]
enum PostStatus: string
{
	case DRAFT = 'draft';

	case PUBLISHED = 'published';

	case ARCHIVED = 'archived';
}
```

The usage here is very simple. You can use the enum type as a type of a field or as an argument type.

```php
namespace App\GraphQL\Posts;

use Graphette\Graphette\Attribute as GQL;

#[GQL\ObjectType]
class Post
{
    #[GQL\Field]
    public PostStatus $status;

}
```

## ResolvingMiddleware

This extension also provides a simple-to-use middleware logic for resolving fields. It's a very useful way how to attach any reusable logic to the resolving process, such as authorization, validation, logging, etc.

You can simply define a middleware by implementing `Graphette\Graphette\Resolving\ResolvingMiddleware` interface. This interface has only one method `__invoke` which    gets passed   all the  properties of standard  resolver function   and also an object of `Graphette\Graphette\TypeRegistry\FieldInfo` class. This object  contains additional informations about the field, such as resolve method  location, or   field's attached attributes.

```php
namespace App\GraphQL\Posts;

use Graphette\Graphette\Attribute as GQL;

class LoggingMiddleware implements Graphette\Graphette\Resolving\ResolvingMiddleware
{
    public function __invoke(
		$objectValue,
		array $args,
		$context,
		ResolveInfo $resolveInfo,
		FieldInfo $fieldInfo,
		callable $next
    ): void
    {
        // here you can do anything you want, such as logging, authorization, validation, etc.
        // you can also modify the arguments
        // and if there is no disruption, you can call the next middleware
		$next($objectValue, $args, $context, $resolveInfo, $fieldInfo);
    }

}
```

These middlewares are called for resolving of every field, so you should be very careful about performance impact.

You can simply register the middleware in a middleware chain through configuration like this:

```neon
graphql:
    resolverMiddlewares:
        - Graphette\Graphette\Resolving\Middleware\ExecutionMiddleware
```

Don't forget to register the ExecutionMiddleware (usually as the last one), which is responsible for executing the field's resolve method.
