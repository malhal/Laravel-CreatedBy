<?php
/**
 *  Laravel-CreatedBy (http://github.com/malhal/Laravel-CreatedBy)
 *
 *  Created by Malcolm Hall on 27/8/2016.
 *  Copyright © 2016 Malcolm Hall. All rights reserved.
 */

namespace Malhal\CreatedBy;

use \Illuminate\Database\Eloquent\Scope;
use \Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

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