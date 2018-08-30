<?php
/**
 * Created by PhpStorm.
 * User: Master
 * Date: 7/26/2018
 * Time: 11:22 AM
 */
require_once('vendor/autoload.php');

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;


class Scrape
{
    /**
     * @var Client
     */
    private $webClient;
    /**
     * @var DOMDocument
     */
    private $dom;

    private $parentnode;

    private $title;

    private $html;

    /**
     * Init scraper to scrape $site
     * @param string $site Site to scrape
     * @param int $timeout seconds before request times out.
     */
    public function __construct($site, $timeout = 2)
    {
        $this->webClient = new Client([
            'base_uri' => $site,
            'timeout' => $timeout
        ]);
    }

    /**
     * Load sub page to site.
     * E.g, '/' loads the site root page
     * @param string $page Page to load
     * @return $this
     */
    public function load($page) {

        try {
            $response = $this->webClient->get($page, ['verify' => false]);
        } catch(ConnectException $e) {
            throw new \RuntimeException(
                $e->getHandlerContext()['error']
            );
        }

        $html = $response->getBody();

        $this->dom = new DOMDocument;

        // Ignore errors caused by unsupported HTML5 tags
        libxml_use_internal_errors(true);
        $this->dom->loadHTML($html);
        libxml_clear_errors();

        return $this;
    }

    /**
     * Get first nodes matching xpath query
     * below parent node in DOM tree
     * @param $xpath string selector to query the DOM
     * @param $parent \DOMNode to use as query root node
     * @return \DOMNode
     */
    public function getNode($xpath, $parent=null) {
        $nodes = $this->getNodes($xpath, $parent);

        if ($nodes->length === 0) {
            throw new \RuntimeException("No matching node found");
        }

        return $nodes[0];
    }

    /**
     * Get all nodes matching xpath query
     * below parent node in DOM tree
     * @param $xpath string selector to query the DOM
     * @param $parent \DOMNode to use as query root node
     * @return \DOMNodeList
     */
    public function getNodes($xpath, $parent=null) {
        $DomXpath = new DOMXPath($this->dom);
        $nodes = $DomXpath->query($xpath, $parent);
        return $nodes;
    }

    public function setParentNode($xpath) {
        $this->parentnode = $this->getNode($xpath);
        return $this;
    }

    public function setNodeTitle($xpath) {
        $this->title = $this->getNode($xpath)->nodeValue;
        return $this;
    }

    public function getTitle() {
        return $this->title;
    }

    /**
     * returns the html string of dom
     */
    public function getHtml() {
        $doc = $this->parentnode->ownerDocument;
        return $doc->saveHtml($this->parentnode);
    }
}