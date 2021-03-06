# ZendX_View_Autoescaping

This project provides you a ViewRenderer with auto escaping of all assigned view variables. It also prevents you to use object variables within view skripts to call methods.

See the examples and tests to understand the facade concept.

[![Build Status](https://travis-ci.org/jensklose/ZendX_View_Autoescaping.png?branch=master)](https://travis-ci.org/jensklose/ZendX_View_Autoescaping)

## Requirements

* Zend Framework 1.X
* PHP 5.2.x (PHPUnit 3.6 requires PHP 5.2.7 or later)
* PHPUnit >= 3.5 (for testing and development)

## Installation

### Composer
    ...
    "require": {
        "zendx/viewautoescape": "~1.5",
    }

    

### Download
Copy the sources to your project library path and add the ZendX namespace to project autoloader.

    autoloaderNamespaces[] = "ZendX_"

## Configuration
Init the view in your bootstrap.php

    protected function _initView()
    {
        $resources = $this->getOption('resources');
        $options = array();
        if (isset($resources['view'])) {
            $options = $resources['view'];
        }
        $view = new ZendX_View_Autoescape($options);

        if (isset($options['doctype'])) {
            $view->doctype()->setDoctype(strtoupper($options['doctype']));
            if (isset($options['charset']) && $view->doctype()->isHtml5()) {
                $view->headMeta()->setCharset($options['charset']);
            }
        }
        if (isset($options['contentType'])) {
            $view->headMeta()->appendHttpEquiv('Content-Type', $options['contentType']);
        }
        
        $viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer();
        $viewRenderer->setView($view);
        Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);
        return $view;
    }

## Examples
Controller

    public function indexAction()
    {
        $this->view->productClass = 'simpleString';
        $this->view->products = array(
            'make<XSS>' => array(
                'name' => '<i>Hacking</i> Session',
                'price' => 672.45
        );
    }


View script

    <h3><?php echo $this->productClass ?></h3>
    <div id="products" class="productList">
        <?php foreach ($this->products as $escapedKey => $product): ?>
            <div id="product<?php echo $escapedKey ?>">
                <?php echo $product->html('name') ?> <strong>[<?php echo $product->html('price') ?> €]</strong>
            </div>
        <?php endforeach; ?>
    </div>
