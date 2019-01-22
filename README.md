# IntegralService BehatContext

[![Build Status](https://travis-ci.com/IntegralService/BehatContext.svg?branch=master)](https://travis-ci.com/IntegralService/BehatContext)

## What is it

This project provides behat contexts to tests things such as :
  - API: deal with JWTokens
  - Emails: validate the reception of emails and follow links in a received email
  - Web: check presence of elements in the page, interact with them and deal with form elements (input, select, ...)
  - Coverage: when adding this context to your test suites, a coverage report of the suite will be generated

## How to install

You can install those contexts with composer :

`composer require --dev integralservice/behat-context`

## Configuration

An example of configuration is available in the file `behat.yml.dist`.


## How to contribute

We are more than pleased to receive pull request if you want to add some functionnalities
that can benefit the community.
