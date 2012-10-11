<?php

namespace Megazoll\Owler\Collector;

use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

class HeadHunter
{
    const START_URL = '/employersList.do';
    const URL_PREFIX = 'http://hh.ru';

    private $showAll;
    private $client;

    public function __construct($showAll = false)
    {
        $this->showAll = $showAll;
        $this->client = new Client;
    }

    public function collect()
    {
        $pages = $this->getPageCount();

        $companies = [];
        for ($i = 0; $i < $pages; $i++) {
            $companies = array_merge($companies, $this->collectPage($i));
        }

        return $companies;
    }

    private function getPageCount()
    {
        $crawler = $this->client->request('GET', static::URL_PREFIX.static::START_URL);

        return $crawler->filter('.b-pager-lite a')->last()->text();
    }

    public function collectPage($page = 0)
    {
        $crawler = $this->client->request('GET', static::URL_PREFIX.static::START_URL.'?page='.$page);

        return $this->arrayFlatten($crawler->filter('.b-companylist')->each(function (\DOMElement $node) {
            if (!$node->hasChildNodes()) {
                return [];
            }
            $crawler = new Crawler;
            $crawler->add($node);

            return $crawler->children()->each(function (\DOMElement $node) {
                $crawler = new Crawler;
                $crawler->add($node);

                return [
                    'title'     => $crawler->filter('a')->text(),
                    'vacancies' => $crawler->filter('em')->text()
                ];
            });
        }));
    }

    private function arrayFlatten($a)
    {
        $b = [];
        array_map(function($v) use (&$b) {
            $b = array_merge($b, $v);
        }, $a);

        return $b;
    }
}
