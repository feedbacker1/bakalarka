<?php

require_once "lib/vendor/autoload.php";
require_once "Element.php";

use PHPHtmlParser\Dom;

ini_set('xdebug.var_display_max_depth', 100);
ini_set('xdebug.var_display_max_children', 100);
ini_set('xdebug.var_display_max_data', 100);
ini_set('max_execution_time', 1800);

class Parser
{
    private $elements = [];
    public $closingTags = [];
    public $htmlResult = "";
    public $conn;
    public $pageName;
    public $scripts = [];
    public $scriptCounter = 0;
    public $url = "";
    public $parsedAnchors = [];


    public function __construct($html = false, $pageName = false, $parsedAnchors = false, $filename = false)
    {

        include "db.php";

        $servername = $config['server'];
        $username = $config['user'];
        $password = $config['pass'];
        $dbname = $config['table'];

        $this->conn = new mysqli($servername, $username, $password, $dbname);

        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }

        if ($html) {

            if (is_file($html)) {
                $this->pageName = $html;
            }

            if ($pageName) {
                $this->pageName = $pageName;
            }

            if (strlen($this->pageName) == 0) {
                $this->pageName = "index.html";
            }

            if ($parsedAnchors) {
                $this->parsedAnchors = $parsedAnchors;
            }

            $dom = new Dom;
            $dom->load($html);

            $this->handleScripts($dom->getRaw());

            if (strpos($html, "http") !== false) {
                if ($filename) {
                    $this->url = str_replace($filename, '', $this->url);
                } else {
                    $this->url = $html;
                }
                $this->parse($dom->root, true);
            } else {
                $this->parse($dom->root);
            }

            if (strpos($html, "http") !== false) {
                file_put_contents($this->pageName, $this->toHTML(false, true));
            }
        }
    }

    /**
     * @return array
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * @param array $elements
     */
    public function setElements($elements)
    {
        $this->elements = $elements;
    }

    /**
     * @param Element $element
     */
    public function addElement($element)
    {
        $this->elements[] = $element;
    }

    public function findElementById($id) {
        foreach ($this->elements as $element) {
            if ($element->getId() == $id) {
                return $element;
            }
        }

        return false;
    }

    public function getNextPageNum($string){

        $start = "index";
        $end = ".html";

        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;

        return intval(substr($string, $ini, $len)) + 1;
    }

    public function handleScripts($html) {
        $dom = new DOMDocument();

        $dom->loadHTML($html);

        $script = $dom->getElementsByTagName('script');

        foreach($script as $item)
        {
            $hasSrc = false;
            foreach ($item->attributes as $attribute) {
                if ($attribute->nodeName == "src") {
                    $hasSrc = true;
                }
            }


            if (!$hasSrc) {
                $this->scripts[] = $item->textContent;
            }
        }
    }

    public function setClosingTag($node) {
        if (method_exists($node, 'lastChild') && $node->lastChild()) {
            if ($node->getTag()->name() != "text" && $node->getTag()->name() != "root" && !$node->getTag()->isSelfClosing()) {

                $tagName = $node->getTag()->name();

                $hasChild = true;
                while($hasChild) {
                    if (method_exists($node->lastChild(), 'hasChildren') && $node->lastChild()->hasChildren()) {
                        $node = $node->lastChild();
                    } else {
                        $hasChild = false;
                    }
                }

                if (isset($this->closingTags[$node->lastChild()->id()])) {
                    $this->closingTags[$node->lastChild()->id()] = "</" . $tagName . ">".$this->closingTags[$node->lastChild()->id()];
                } else {
                    $this->closingTags[$node->lastChild()->id()] = "</" . $tagName . ">";
                }
            }
        }

        if (!$node->hasChildren() || $node->getTag()->name() == "script") {
            if (isset($this->closingTags[$node->id()])) {
                $this->closingTags[$node->id()] = "</" . $node->getTag()->name() . ">".$this->closingTags[$node->id()];
            } else {
                $this->closingTags[$node->id()] = "</" . $node->getTag()->name() . ">";
            }
        }
    }

    public function parse($node, $handleAnchors = false) {

        if (in_array('data-bakcms-no-parse', array_keys($node->getTag()->getAttributes()))) {
            return;
        }

        if (isset($node->getTag()->getAttributes()['src']) && strpos($node->getTag()->getAttributes()['src']['value'],'ckeditor') !== false) {
            return;
        }


        $element = new Element();
        $element->setId($node->id());
        $element->setTag($node->getTag()->name());
        $attrs = [];

        foreach ($node->getTag()->getAttributes() as $attr => $value) {
            if ($attr != "data-bakcms-id") {
                $attrs[$attr] = $value['value'];
            }
        }





        //TODO ak sa dostanem k prvemu A tagu na indexe - tak len ho zapíš a až potom možno zparsuj

        //parsed anchors je pole kde kluc je povodny odkaz a value nový (funkčný po zparsovaní)

        $href = $node->getTag()->getAttributes()['href']['value'];

        if ($handleAnchors && $node->getTag()->name() == "a" && isset($href) && $href != "#") {

            if (!key_exists($href, $this->parsedAnchors)) {

                $this->parsedAnchors[$href] = $href.".html";
                $this->parsedAnchors['url'] = $this->url;

                //if (strpos($href, "//") === false) {

                //$parser = new Parser($this->url . "/" . $href, $newPageName, $this->parsedAnchors, $href);

                //toto je rekurzia

                //$parser->downloadContent();
                //$parser->toDB();

                //}

                $attrs['href'] = $href.".html";

            } else {

                $attrs['href'] = $this->parsedAnchors[$href];

            }

        }










        $element->setAttr($attrs);

        // alebo > 0 (children)
        if (!(method_exists($node, "hasChildren") && $node->hasChildren() && count($node->getChildren()) == 1 && $node->firstChild()->getTag()->name() == "text")) {
            $element->setText($node->text);
        } else {
            $element->setText('');
        }

        if ($node->getTag()->isSelfClosing()) {
            $element->setSelfClosing();
        }

        if ($node->getParent()) {
            $element->setParent($node->getParent()->id());
        }

        if (!($element->getTag() == "text" && strlen(trim($element->getText()))  < 1)) {
            if ($element->getTag() !== "root") {
                $this->addElement($element);
            }
        }

//        if ($node->getParent() && $node->getParent()->getTag()->name() == "script") {
//            array_pop($this->getElements());
//        }


        if ($node->getTag()->name() != "text" && $node->getTag()->name() != "root" && !$node->getTag()->isSelfClosing()) {
            $this->setClosingTag($node);
        }

        if ($node->getTag()->name() == "script" && !isset($node->getTag()->getAttributes()['src'])) {
            $element = new Element();
            $element->setId("aaa");
            $element->setTag("text");
            $element->setText($this->scripts[$this->scriptCounter++]."\n</script>");
            $element->setParent($node->id());
            $this->addElement($element);

            return;
        }

        if (method_exists($node, 'getChildren')) {
            foreach ($node->getChildren() as $child) {
                $this->parse($child, $handleAnchors);
            }
        }

    }

    public function rel2abs($rel, $base)
    {
        // parse base URL  and convert to local variables: $scheme, $host,  $path
        extract( parse_url( $base ) );

        if ( strpos( $rel,"//" ) === 0 ) {
            return $scheme . ':' . $rel;
        }

        // return if already absolute URL
        if ( parse_url( $rel, PHP_URL_SCHEME ) != '' ) {
            return $rel;
        }

        // queries and anchors
        if ( $rel[0] == '#' || $rel[0] == '?' ) {
            return $base . $rel;
        }

        // remove non-directory element from path
        $path = preg_replace( '#/[^/]*$#', '', $path );

        // destroy path if relative url points to root
        if ( $rel[0] ==  '/' ) {
            $path = '';
        }

        // dirty absolute URL
        $abs = $host . $path . "/" . $rel;

        // replace '//' or  '/./' or '/foo/../' with '/'
        $abs = preg_replace( "/(\/\.?\/)/", "/", $abs );
        $abs = preg_replace( "/\/(?!\.\.)[^\/]+\/\.\.\//", "/", $abs );

        // absolute URL is ready!
        return $scheme . '://' . $abs;
    }

    public function downloadImagesInCss($content, $url) {

        $search = '%url\s*\(\s*[\\\'"]?(?!(((?:https?:)?\/\/)|(?:data:?:)))([^\\\'")]+)[\\\'"]?\s*\)%';
        preg_match_all($search, $content, $matches);

        if (!file_exists(__DIR__ .'\..\images')) {
            mkdir(__DIR__ .'\..\images', 0777, true);
        }

        if (isset($matches[3])) {
            foreach ($matches[3] as $match) {

                $image = file_get_contents($this->rel2abs($match, $url));

                if (strpos($match, "?") !== false)
                    $match = strstr($match, '?', true);

                file_put_contents(__DIR__ . "\..\images/" . basename($match), $image);

                $content = str_replace($match, "../images/" . basename($match), $content);

            }
        }

        return $content;

    }

    public function downloadContent() {

        foreach ($this->getElements() as $element) {

            //js
            if ($element->getTag() == "script" && isset($element->getAttr()['src'])) {

                if (!file_exists(__DIR__ .'\..\js')) {
                    mkdir(__DIR__ .'\..\js', 0777, true);
                }

                if (strpos($element->getAttr()['src'], "//") === false) {
                    $content = file_get_contents($this->url."/".$element->getAttr()['src']);
                } else {
                    $content = file_get_contents($element->getAttr()['src']);
                }

                if (strpos($element->getAttr()['src'], "?") !== false) {
                    $filename = basename(strtok($element->getAttr()['src'], '?'));
                } else {
                    $filename = basename($element->getAttr()['src']);
                }

                if (!file_exists(__DIR__ ."\..\js/".$filename)) {
                    file_put_contents(__DIR__ . "\..\js/" . $filename, $content);
                }

                $attr = $element->getAttr();
                $attr['src'] = "js/".basename($element->getAttr()['src']);
                $element->setAttr($attr);

            //css
            } else if ($element->getTag() == "link" && isset($element->getAttr()['href'])) {

                if (!file_exists(__DIR__ .'\..\css')) {
                    mkdir(__DIR__ .'\..\css', 0777, true);
                }

                if (strpos($element->getAttr()['href'], "//") === false) {
                    $content = file_get_contents($this->url."/".$element->getAttr()['href']);

                    $content = $this->downloadImagesInCss($content, $this->url."/".$element->getAttr()['href']);

                    if (strpos($element->getAttr()['href'], "?") !== false) {
                        $filename = basename(strtok($element->getAttr()['href'], '?'));
                    } else {
                        $filename = basename($element->getAttr()['href']);
                    }

                    if (!file_exists(__DIR__ ."\..\css/".$filename)) {
                        file_put_contents(__DIR__ ."\..\css/".$filename, $content);
                    }

                    $attr = $element->getAttr();
                    $attr['href'] = "css/".basename($element->getAttr()['href']);
                    $element->setAttr($attr);

                }

            //img
            } if ($element->getTag() == "img" && isset($element->getAttr()['src'])) {

                if (!file_exists(__DIR__ .'\..\images')) {
                    mkdir(__DIR__ .'\..\images', 0777, true);
                }

                if (strpos($element->getAttr()['src'], "//") === false) {
                    $content = file_get_contents($this->url."/".$element->getAttr()['src']);
                } else {
                    $content = file_get_contents($element->getAttr()['src']);
                }

                if (!file_exists(__DIR__ ."\..\images/".basename($element->getAttr()['src']))) {
                    file_put_contents(__DIR__ ."\..\images/".basename($element->getAttr()['src']), $content);
                }

                $attr = $element->getAttr();
                $attr['src'] = "images/".basename($element->getAttr()['src']);
                $element->setAttr($attr);

            }

        }

    }

    public function toHTML($withLiveEdit = false, $generateFile = false) {

        $notLiveEditTags = ["html", "head", "link", "text", "script", "body", "title"];

        foreach ($this->getElements() as $element) {

            $str = "";

            if ($element->getTag() == "text") {
                $str .= $element->getText();
            } else {
                $str .= "<" . $element->getTag();
            }

            if ($element->getTag() == "script" && sizeof($element->getAttr()) == 0) {
                $str .= ">";
            }

            if ($element->getAttr()) {
                foreach ($element->getAttr() as $attr => $value) {
                    if ($generateFile) {
                        if ($attr != "data-bakcms-id") {
                            $str .= " " . $attr . "=\"" . $value . "\"";
                        }
                    } else {
                        $str .= " " . $attr . "=\"" . $value . "\"";
                    }
                }
            }

            if ($withLiveEdit) {
                if (!in_array($element->getTag(), $notLiveEditTags)) {
                    $str .= " data-bakcms-id=\"" . $element->getId() . "\"";
                }
            }

            if ($element->isSelfClosing()) {
                $str .= " />";
            } else if ($element->getTag() !== "text" && !($element->getTag() == "script" && sizeof($element->getAttr()) == 0)) {
                $str .= ">";
            }

            //print closing tags
            if (isset($this->closingTags[$element->getId()])) {
                if ($element->getTag() != "script") {
                    $str .= "\n".$this->closingTags[$element->getId()];
                } else if (isset($element->getAttr()['src'])) {
                    $str .= $this->closingTags[$element->getId()];
                }
            }



            // handle dynamicly created elements
            if ($element->getTag() == "body") {
                if (!$generateFile) {
                    $str .= "<script data-bakcms-no-parse>document.body.addEventListener('DOMNodeInserted', function(e) { if(e.target.nodeType == 1) { e.target.setAttribute('data-bakcms-no-parse', '') }}) </script>";
                }
            }



            $str .= "\n";

            $this->htmlResult .= $str;

        }

        if ($withLiveEdit) {

            $this->htmlResult .= "<script data-bakcms-no-parse src=\"cms/liveedit.js\"></script>";

            return "<!DOCTYPE html>\n<html>\n".$this->htmlResult."\n</html>";

        } else {

            return "<!DOCTYPE html>\n<html>\n".$this->htmlResult."\n</html>";

            require("htmLawed.php");
            $tidy = hl_tidy($this->htmlResult, 't', 'div');
            return "<!DOCTYPE html>\n<html>\n".$tidy."\n</html>";

        }

    }

    public function toFile($filename = false) {

        if (!$filename) {
            $name = $this->pageName;
        } else {
            $name = $filename;
        }

        $this->fromDB($name);

        if(file_put_contents("../".$name, $this->toHTML(false, true))) {
            echo "success";
        } else {
            echo "false";
        }

    }

    public function toDB($filename = false) {

        if (!$filename) {
            $name = $this->pageName;
        } else {
            $name = $filename;
        }

        $tags = json_encode($this->closingTags);

        $stmt = $this->conn->prepare("INSERT INTO pages (name, closing_tags) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $tags);
        $stmt->execute();
        $last_id = $stmt->insert_id;
        $stmt->close();

        foreach ($this->getElements() as $element) {

            $id = $element->getId();
            $tag = $element->getTag();
            $attr = json_encode($element->getAttr());
            $text = $element->getText();
            $parent = $element->getParent();
            $selfClosing = intval($element->isSelfClosing());

            $stmt = $this->conn->prepare("INSERT INTO elements (element_id, page_id, tag, attr, text, parent_id, self_closing) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssi", $id, $last_id, $tag, $attr, $text, $parent, $selfClosing);
            $stmt->execute();

            $stmt->close();

        }

    }

    public function fromDB($page_id) {

        $page_id = $this->conn->escape_string($page_id);

        if (is_numeric($page_id)) {
            $sql = "SELECT closing_tags FROM pages WHERE id = '$page_id' ORDER BY id DESC LIMIT 1";
        } else {
            $sql = "SELECT closing_tags FROM pages WHERE name = '$page_id' ORDER BY id DESC LIMIT 1";
        }
        $result = $this->conn->query($sql);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $this->closingTags = json_decode($row['closing_tags'], true);
        }

        if (is_numeric($page_id)) {
            $sql = "SELECT * FROM elements WHERE page_id = '$page_id'";
        } else {
            $sql = "SELECT * FROM elements WHERE page_id = (SELECT id FROM pages WHERE name = '$page_id' ORDER BY id DESC LIMIT 1)";
        }
        $result = $this->conn->query($sql);

        if ($result->num_rows > 0) {

            $this->setElements([]);

            while($row = $result->fetch_assoc()) {
                $element = new Element();
                $element->setId($row['element_id']);
                $element->setTag($row['tag']);
                $element->setAttr(json_decode($row['attr'], true));
                $element->setText($row['text']);
                $element->setparent($row['parent_id']);
                if ($row['self_closing']) {
                    $element->setSelfClosing();
                }

                $this->addElement($element);

            }
        } else {
            echo "EMPTY TABLE";
        }
    }

    public function getElementHtmlFromDB($id) {

        $id = $this->conn->escape_string($id);

        $sql = "SELECT closing_tags, page_id FROM elements LEFT JOIN pages ON page_id = pages.id WHERE elements.id = '$id' LIMIT 1";

        $result = $this->conn->query($sql);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $this->closingTags = json_decode($row['closing_tags'], true);
            $page_id = $row['page_id'];
        } else {
            return;
        }

        $sql = "SELECT * FROM elements WHERE id = '$id' LIMIT 1";

        $result = $this->conn->query($sql);

        $this->setElements([]);

        if ($result->num_rows > 0) {

            $row = $result->fetch_assoc();

            $element = new Element();
            $element->setId($row['element_id']);
            $element->setTag($row['tag']);
            $element->setAttr(json_decode($row['attr'], true));
            $element->setText($row['text']);
            $element->setparent($row['parent_id']);
            if ($row['self_closing']) {
                $element->setSelfClosing();
            }

            $this->addElement($element);

            $this->recursiveGetElements($row['element_id'], $page_id);

            echo $this->toHTML();

        } else {
            echo "EMPTY TABLE";
        }
    }

    public function recursiveGetElements($el, $page_id) {

        $sql = "SELECT * FROM elements WHERE parent_id = '" . $el . "' AND page_id = '$page_id'";

        $result = $this->conn->query($sql);
        if ($result->num_rows > 0) {

            while ($row = $result->fetch_assoc()) {

                $element = new Element();
                $element->setId($row['element_id']);
                $element->setTag($row['tag']);
                $element->setAttr(json_decode($row['attr'], true));
                $element->setText($row['text']);
                $element->setparent($row['parent_id']);
                if ($row['self_closing']) {
                    $element->setSelfClosing();
                }

                $this->addElement($element);

                $this->recursiveGetElements($row['element_id'], $page_id);

            }

        }

    }

}