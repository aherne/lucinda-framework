<?php
/**
 * Collects information about logged in OAuth2 user:
 * - id: unique user identifier of remote logged in user (usually a number)
 * - name: name of remote logged in user (usually first name followed by last name)
 * - email: email of remote logged in user
 */
abstract class AbstractUserInformation implements \Lucinda\WebSecurity\OAuth2UserInformation {
	protected $id;
	protected $name;
	protected $email;

    /**
     * {@inheritDoc}
     * @see \Lucinda\WebSecurity\OAuth2UserInformation::getId()
     */
	public function getId() {
		return $this->id;
	}

    /**
     * {@inheritDoc}
     * @see \Lucinda\WebSecurity\OAuth2UserInformation::getName()
     */
	public function getName() {
		return $this->name;
	}

    /**
     * {@inheritDoc}
     * @see \Lucinda\WebSecurity\OAuth2UserInformation::getEmail()
     */
	public function getEmail() {
		return $this->id;
	}
}