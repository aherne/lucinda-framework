<?php
abstract class AbstractUserInformation implements OAuth2UserInformation {
	protected $id;
	protected $name;
	protected $email;
	
	public function getId() {
		return $this->id;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function getEmail() {
		return $this->id;
	}
}