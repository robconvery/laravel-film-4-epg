<?php declare(strict_types=1);
/**
 * Class Film4
 *
 * @author robconvery <robconvery@me.com>
 */

namespace Robconvery\laravelFilm4Epg;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Collection;
use Psr\Http\Message\ResponseInterface;

class Film4
{
    protected $date;
    protected $classname = "channel-F4";
    protected static $uri = 'https://www.channel4.com/tv-guide/';

    /**
     * Film4 constructor.
     * @param Carbon $date
     */
    public function __construct(Carbon $date)
    {
        $this->date = $date;
    }

    /**
     * @return Collection
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function extract(): Collection
    {
        return $this->allFilms($this->getContent())
            ->filter(function ($film){
                return $film['title'] != 'Off air';
            });
    }

    /**
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function getContent(): string
    {
        return $this->getData($this->date)
            ->getBody()
            ->getContents();
    }

    /**
     * @param string $html
     * @return Collection
     */
/*    private function getFilms(string $html): Collection
    {
        return $this->allFilms($html);
    }*/

    /**
     * @param string $html
     * @return Collection
     */
    private function allFilms(string $html)
    {
        $data = collect();
        foreach ($this->getChannel($html) as $ele) {
            if ($ele->hasChildNodes()) {
                foreach ($ele->childNodes as $node) {
                    if ($node->tagName == 'div' && $node->hasChildNodes()) {
                        foreach ($node->childNodes as $article) {
                            $films = $this->loadDocument($this->getHtml($article),'ep-info');
                            foreach ($films as $film) {
                                $data->push([
                                    'title' => $film->lastChild->nodeValue,
                                    'time' => $film->firstChild->nodeValue
                                ]);
                            }
                        }
                    }
                }
            }
        }
        return $data;
    }

    /**
     * @param string $html
     * @return \DOMNodeList
     */
    protected function getChannel(string $html)
    {
        return $this->loadDocument($html, $this->classname);
    }

    /**
     * @param \DOMElement $film
     * @param string $class
     * @return string
     */
    protected function getFilmText(\DOMElement $film, string $class)
    {
        return $this->loadDocument($this->getHtml($film), $class)
            ->item(0)
            ->nodeValue;
    }

    /**
     * @param \DOMElement $node
     * @return string
     */
    public function getHtml(\DOMElement $node)
    {
        return $node->ownerDocument->saveHTML($node);
    }

    /**
     * @param string $html
     * @return \DOMNodeList
     */
    private function loadDocument(string $html, $classname)
    {
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->validateOnParse = true;
        $dom->loadHTML($html);
        $finder = new \DomXPath($dom);
        return $finder->query("//*[contains(@class, '$classname')]");
    }

    /**
     * @param Carbon $date
     * @return Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function getData(Carbon $date): Response
    {
        return (new Client())
            ->request('GET', static::$uri . $date->format('Y/m/d'));
    }

    /**
     * @param Carbon $date
     * @return Collection
     */
    public static function films(Carbon $date): Collection
    {
        return (new static($date))->extract();
    }

}
