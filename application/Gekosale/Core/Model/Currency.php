<?php

namespace Gekosale\Core\Model;

class Currency extends Eloquent {

	protected $table = 'currency';
	public $timestamps = true;
	protected $softDelete = false;

}