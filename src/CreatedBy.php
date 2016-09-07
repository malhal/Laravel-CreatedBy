<?php
/**
 *  Laravel-CreatedBy (http://github.com/malhal/Laravel-CreatedBy)
 *
 *  Created by Malcolm Hall on 27/8/2016.
 *  Copyright © 2016 Malcolm Hall. All rights reserved.
 */

namespace Malhal\CreatedBy;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\UnauthorizedException;

trait CreatedBy
{
    // Define any of these constants in your class to override,
    // they are not defined here because of a limitation with traits.

    // If renaming the relation also implement the matching relation method and return createdBy() and updatedBy()
    // const CREATED_BY = 'createdBy';
    // const UPDATED_BY = 'updatedBy';

    protected $disableCreatedBy;

    public static function bootCreatedBy()
    {
        static::addGlobalScope(new CreatedByScope());

        static::creating(function($model){
            $model->updateCreatedBy();
        });

        // by using this event instead of save, if the same update is done by a different user then no change is made.
        static::updating(function($model){
            $model->updateCreatedBy();
        });
    }

    public function updateCreatedBy(){

        if($this->disableCreatedBy){
            return;
        }

        $user = Auth::user();

        if (!$this->isDirty($this->updatedByForeignKey())) {
            $this->setAttribute($this->updatedByForeignKey(), $user->id);
        }

        if (!$this->exists && !$this->isDirty($this->createdByForeignKey())) {
            $this->setAttribute($this->createdByForeignKey(), $user->id);

        }
    }

    public function saveWithoutCreatedBy(array $options = [])
    {
        $this->disableCreatedBy = true;

        $saved = $this->save($options);

        $this->disableCreatedBy = false;

        return $saved;
    }

//    public function getGuarded(){
//        return array_merge(parent::getGuarded(), [$this->createdByForeignKey(), $this->updatedByForeignKey()]);
//    }

//    public function getHidden()
//    {
//        return array_merge(parent::getHidden(), [$this->createdByRelationName(), $this->updatedByRelationName()]);
//    }

    public function createdBy()
    {
        return $this->belongsTo(config('auth.providers.users.model'), $this->createdByForeignKey());
    }

    public function updatedBy()
    {
        return $this->belongsTo(config('auth.providers.users.model'), $this->updatedByForeignKey());
    }

    public function createdByRelationName(){
        return defined('self::CREATED_BY') ? self::CREATED_BY : 'createdBy';
    }

    public function updatedByRelationName(){
        return defined('self::UPDATED_BY') ? self::UPDATED_BY : 'updatedBy';
    }

    public function createdByForeignKey()
    {
        return Str::snake($this->createdByRelationName()).'_id';
    }

    public function updatedByForeignKey()
    {
        return Str::snake($this->updatedByRelationName()).'_id';
    }

    public function scopeWhereCreatedBy(Builder $builder, Model $user)
    {
        $model = $builder->getModel();
        $createdBy = $model->createdByRelationName();
        $createdBy = $model->$createdBy();

        return $builder->where($createdBy->createdByForeignKey(), $user->getKey());
    }
}