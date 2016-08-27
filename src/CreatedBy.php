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
            if($model->disableCreatedBy){
                return;
            }

            $model->updateCreatedBy();
        });

        // by using this event instead of save, if the same update is done by a different user then no change is made.
        static::updating(function($model){
            if($model->disableCreatedBy){
                return;
            }

            $model->updateCreatedBy();
        });
    }

    public function updateCreatedBy(){
        $user = $this->currentUser();

        if (! $this->isDirty($this->updatedByForeignKey())) {
            $updatedBy = $this->updatedByRelationName();
            // we disabled this to prevent the whole user appearing in the output array.
            // the alternative would have been to include a default hidden.
            //$this->$updatedBy()->associate($user);
            $this->setAttribute($this->updatedByForeignKey(), $user->id);
        }

        if (! $this->exists && ! $this->isDirty($this->createdByForeignKey())) {
            $createdBy = $this->createdByRelationName();
            //$this->$createdBy()->associate($user);
            $this->setAttribute($this->createdByForeignKey(), $user->id);
        }
    }

    public function saveWithoutCreatedBy(array $options = [])
    {
        $this->disableCreatedBy = true;

        $$saved = $this->save($options);

        $this->disableCreatedBy = false;

        return $$saved;
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

    protected function createdByForeignKey()
    {
        return Str::snake($this->createdByRelationName()).'_id';
    }

    protected function updatedByForeignKey()
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

    public function currentUser()
    {
        // if we came through a guard then we will have a user or it will have already exceptioned.
        $user = Auth::user();
        if(is_null($user)) {
            // check if they used a token otherwise they will be a guest.
            $user = Auth::guard('api')->user();
            if (!$user && !is_null(Auth::guard('api')->getTokenForRequest())) {
                throw new UnauthorizedException('Invalid token');
            }
        }
        return $user;
    }
}