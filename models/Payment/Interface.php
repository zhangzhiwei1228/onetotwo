<?php

interface Payment_Interface {
	public function pay($amount, $params);
	public function callback();
}