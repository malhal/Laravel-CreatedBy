# Laravel-CreatedBy
A trait that adds createdBy and updatedBy user relations to your models.

First either update your database or add this to a migration for each model:

    $table->unsignedInteger('created_by_id');
    $table->unsignedInteger('updated_by_id');
    $table->foreign('created_by_id')
        ->references('id')->on('users')
        ->onDelete('cascade');
    $table->foreign('updated_by_id')
        ->references('id')->on('users')
        ->onDelete('cascade');

Finally in your model use:

    use CreatedBy;

Now whenever a model is created or updated the createdBy and updatedBy relations will be automatically updated.

Unfortunately you shouldn't use this trait on the User model when using auto-increment keys, this is due to the limitation in MySQL that a field cannot reference the currently being inserted ID. However you can still make use of the [Laravel-CreatedByPolicy](https://github.com/malhal/Laravel-CreatedByPolicy) with the workaround explained in that project. If you are using UUIDs for keys then this problem won't happen.

There are some extra scope features, e.g.

To add the join to retrieve the user info from the user table:

    $query->withCreatedBy()
    $query->withUpdatedBy()
    
To query based on a user:

    $query->whereCreatedBy($user)
    
To temporarily disable updates on a save:

    $model->saveWithoutCreatedBy();

If you would like to utilise this trait as part of a simple secuity model check out the extension of this trait [Laravel-CreatedByPolicy](https://github.com/malhal/Laravel-CreatedByPolicy).
    
## Installation

[PHP](https://php.net) 5.6.4+ and [Laravel](http://laravel.com) 5.3+ are required.

To get the latest version of Laravel CreatedBy, simply require the project using [Composer](https://getcomposer.org):

```bash
$ composer require malhal/laravel-createdby dev-master
```