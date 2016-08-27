<?php

namespace App;

use Illuminate\Auth\Access\AuthorizationException;
use \Illuminate\Database\Eloquent\Scope;
use \Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpFoundation\Response;

class CreatedByScope implements Scope
{
    /**
     * All of the extensions to be added to the builder.
     *
     * @var array
     */
    protected $extensions = ['WithCreatedBy', 'WithUpdatedBy'];

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        // We won't add the joins by default.
    }

    /**
     * Extend the query builder with the needed functions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    public function extend(Builder $builder)
    {
        foreach ($this->extensions as $extension) {
            $this->{"add{$extension}"}($builder);
        }
    }

    protected function addWithCreatedBy(Builder $builder) {
        $builder->macro('withCreatedBy', function (Builder $builder) {
            $model = $builder->getModel();
            $createdBy = $model->createdByRelationName();
            return $builder->with($createdBy);
        });
    }

    protected function addWithUpdatedBy(Builder $builder) {
        $builder->macro('withUpdatedBy', function (Builder $builder) {
            $model = $builder->getModel();
            $updatedBy = $model->updatedByRelationName();
            return $builder->with($updatedBy);
        });
    }
}