<?php

class Service_Copyright_Readability {
    // 淇濆瓨鍒ゅ畾缁撴灉鐨勬爣璁颁綅鍚嶇О
    const ATTR_CONTENT_SCORE = "contentScore";

    // DOM 瑙ｆ瀽绫荤洰鍓嶅彧鏀??鎸?? UTF-8 缂栫爜
    const DOM_DEFAULT_CHARSET = "utf-8";

    // 褰撳垽瀹氬け璐ユ椂鏄剧ず鐨勫唴瀹??
    const MESSAGE_CAN_NOT_GET = "Service_Copyright_Readability was unable to parse this page for content.";

    // DOM 瑙ｆ瀽绫伙紙PHP5 宸插唴缃??锛??
    protected $DOM = null;

    // 闇??瑕佽В鏋愮殑婧愪唬鐮??
    protected $source = "";

    // 绔犺妭鐨勭埗鍏冪礌鍒楄〃
    private $parentNodes = array();

    // 闇??瑕佸垹闄ょ殑鏍囩????
    // Note: added extra tags from https://github.com/ridcully
    private $junkTags = Array("style", "form", "iframe", "script", "button", "input", "textarea", 
                                "noscript", "select", "option", "object", "applet", "basefont",
                                "bgsound", "blink", "canvas", "command", "menu", "nav", "datalist",
                                "embed", "frame", "frameset", "keygen", "label", "marquee", "link");

    // 闇??瑕佸垹闄ょ殑灞炴????
    private $junkAttrs = Array("style", "class", "onclick", "onmouseover", "align", "border", "margin");


    /**
     * 鏋勯??犲嚱鏁??
     *      @param $input_char 瀛楃??︿覆鐨勭紪鐮併??傞粯璁?? utf-8锛屽彲浠ョ渷鐣??
     */
    /**
     * @param
     * @return
     */
    function __construct($source, $input_char = "utf-8") {
        $this->source = $source;

        // DOM 瑙ｆ瀽绫诲彧鑳藉??勭悊 UTF-8 鏍煎紡鐨勫瓧绗??
        $source = mb_convert_encoding($source, 'HTML-ENTITIES', $input_char);

        // 棰勫??勭悊 HTML 鏍囩??撅紝鍓旈櫎鍐椾綑鐨勬爣绛剧瓑
        $source = $this->preparSource($source);
        // 鐢熸垚 DOM 瑙ｆ瀽绫??
        $this->DOM = new DOMDocument('1.0', $input_char);
	try {
            //libxml_use_internal_errors(true);
            // 浼氭湁浜涢敊璇??淇℃伅锛屼笉杩囦笉瑕佺揣 :^)
	    if (!@$this->DOM->loadHTML('<?xml encoding="'.Service_Copyright_Readability::DOM_DEFAULT_CHARSET.'">'.$source)) {
                throw new Exception("Parse HTML Error!");
            }
            foreach ($this->DOM->childNodes as $item) {
                if ($item->nodeType == XML_PI_NODE) {
                    $this->DOM->removeChild($item); // remove hack
                }
            }

            // insert proper
            $this->DOM->encoding = Service_Copyright_Readability::DOM_DEFAULT_CHARSET;
        } catch (Exception $e) {
		// ...
		//echo "There is something wrong! $e\n";
        }
    }


    /**
     * 棰勫??勭悊 HTML 鏍囩??撅紝浣垮叾鑳藉??熷噯纭??琚?? DOM 瑙ｆ瀽绫诲??勭悊
     *
     * @return String
     */
    /**
     * @param
     * @return
     */
    private function preparSource($string) {
        // 鍓旈櫎澶氫綑鐨?? HTML 缂栫爜鏍囪??帮紝閬垮厤瑙ｆ瀽鍑洪敊
        preg_match("/charset=[^\w]?([-\w]+)/i", $string, $match);
        if (isset($match[1])) {
            $string = preg_replace("/charset=[^\w]?([-\w]+)/i", "", $string, 1);
        }

        // Replace all doubled-up <BR> tags with <P> tags, and remove fonts.
        $string = preg_replace("/<br\/?>[ \r\n\s]*<br\/?>/i", "</p><p>", $string);
        $string = preg_replace("/<\/?font[^>]*>/i", "", $string);

        // @see https://github.com/feelinglucky/php-readability/issues/7
        //   - from http://stackoverflow.com/questions/7130867/remove-script-tag-from-html-content
       // $string = preg_replace("#<script(.*?)>(.*?)</script>#is", "", $string);
//var_dump($string);
        return trim($string);
    }


    /**
     * 鍒犻櫎 DOM 鍏冪礌涓??鎵??鏈夌殑 $TagName 鏍囩????
     *
     * @return DOMDocument
     */
    /**
     * @param
     * @return
     */
    private function removeJunkTag($RootNode, $TagName) {
        
        $Tags = $RootNode->getElementsByTagName($TagName);
        
        //Note: always index 0, because removing a tag removes it from the results as well.
        while($Tag = $Tags->item(0)){
            $parentNode = $Tag->parentNode;
            $parentNode->removeChild($Tag);
        }
        
        return $RootNode;
        
    }

    /**
     * 鍒犻櫎鍏冪礌涓??鎵??鏈変笉闇??瑕佺殑灞炴????
     */
    /**
     * @param
     * @return
     */
    private function removeJunkAttr($RootNode, $Attr) {
        $Tags = $RootNode->getElementsByTagName("*");

        $i = 0;
        while($Tag = $Tags->item($i++)) {
            $Tag->removeAttribute($Attr);
        }

        return $RootNode;
    }

    /**
     * 鏍规嵁璇勫垎鑾峰彇椤甸潰涓昏??佸唴瀹圭殑鐩掓ā鍨??
     *      鍒ゅ畾绠楁硶鏉ヨ嚜锛歨ttp://code.google.com/p/arc90labs-readability/   
     *      杩欓/噷鐢遍儜鏅撳崥瀹㈣浆鍙??
     * @return DOMNode
     */
    /**
     * @param
     * @return
     */
    private function getTopBox() {
        // 鑾峰緱椤甸潰鎵??鏈夌殑绔犺妭
        $allParagraphs = $this->DOM->getElementsByTagName("p");

        // Study all the paragraphs and find the chunk that has the best score.
        // A score is determined by things like: Number of <p>'s, commas, special classes, etc.
        $i = 0;
        while($paragraph = $allParagraphs->item($i++)) {
            $parentNode   = $paragraph->parentNode;
            $contentScore = intval($parentNode->getAttribute(Service_Copyright_Readability::ATTR_CONTENT_SCORE));
            $className    = $parentNode->getAttribute("class");
            $id           = $parentNode->getAttribute("id");
            //var_dump($className);
            //var_dump($id);

            // Look for a special classname
            if (preg_match("/(comment|meta|footer|footnote)/i", $className)) {
                $contentScore -= 50;
            } else if(preg_match(
                "/((^|\\s)(post|hentry|entry[-]?(content|text|body)?|article[-]?(content|text|body)?)(\\s|$))/i",
                $className)) {
                $contentScore += 25;
            }

            // Look for a special ID
            if (preg_match("/(comment|meta|footer|footnote)/i", $id)) {
                $contentScore -= 50;
            } else if (preg_match(
                "/^(post|hentry|entry[-]?(content|text|body)?|article[-]?(content|text|body)?)$/i",
                $id)) {
                $contentScore += 25;
            }

            // Add a point for the paragraph found
            // Add points for any commas within this paragraph
            if (strlen($paragraph->nodeValue) > 10) {
                $contentScore += strlen($paragraph->nodeValue);
            }
//var_dump($contentScore);
            // 淇濆瓨鐖跺厓绱犵殑鍒ゅ畾寰楀垎
            $parentNode->setAttribute(Service_Copyright_Readability::ATTR_CONTENT_SCORE, $contentScore);

            // 淇濆瓨绔犺妭鐨勭埗鍏冪礌锛屼互渚夸笅娆″揩閫熻幏鍙??
            array_push($this->parentNodes, $parentNode);
        }
        
        
        /*
       $allParagraphs = $this->DOM->getElementsByTagName("pre");
        

        // Study all the paragraphs and find the chunk that has the best score.
        // A score is determined by things like: Number of <p>'s, commas, special classes, etc.
        $i = 0;
        while($paragraph = $allParagraphs->item($i++)) {
            $parentNode   = $paragraph->parentNode;
            $contentScore = intval($parentNode->getAttribute(Service_Copyright_Readability::ATTR_CONTENT_SCORE));
            $className    = $parentNode->getAttribute("class");
            $id           = $parentNode->getAttribute("id");

            // Look for a special classname
            if (preg_match("/(comment|meta|footer|footnote)/i", $className)) {
                $contentScore -= 50;
            } else if(preg_match(
                "/((^|\\s)(post|hentry|entry[-]?(content|text|body)?|article[-]?(content|text|body)?)(\\s|$))/i",
                $className)) {
                $contentScore += 25;
            }

            // Look for a special ID
            if (preg_match("/(comment|meta|footer|footnote)/i", $id)) {
                $contentScore -= 50;
            } else if (preg_match(
                "/^(post|hentry|entry[-]?(content|text|body)?|article[-]?(content|text|body)?)$/i",
                $id)) {
                $contentScore += 25;
            }

            // Add a point for the paragraph found
            // Add points for any commas within this paragraph
            if (strlen($paragraph->nodeValue) > 10) {
                $contentScore += strlen($paragraph->nodeValue);
            }

            // 淇濆瓨鐖跺厓绱犵殑鍒ゅ畾寰楀垎
            $parentNode->setAttribute(Service_Copyright_Readability::ATTR_CONTENT_SCORE, $contentScore);

            // 淇濆瓨绔犺妭鐨勭埗鍏冪礌锛屼互渚夸笅娆″揩閫熻幏鍙??
            array_push($this->parentNodes, $parentNode);
        }
       
        $allParagraphs = $this->DOM->getElementsByTagName("span");
        

        // Study all the paragraphs and find the chunk that has the best score.
        // A score is determined by things like: Number of <p>'s, commas, special classes, etc.
        $i = 0;
        while($paragraph = $allParagraphs->item($i++)) {
            $parentNode   = $paragraph->parentNode;
            $contentScore = intval($parentNode->getAttribute(Service_Copyright_Readability::ATTR_CONTENT_SCORE));
            $className    = $parentNode->getAttribute("class");
            $id           = $parentNode->getAttribute("id");

            // Look for a special classname
            if (preg_match("/(comment|meta|footer|footnote)/i", $className)) {
                $contentScore -= 50;
            } else if(preg_match(
                "/((^|\\s)(post|hentry|entry[-]?(content|text|body)?|article[-]?(content|text|body)?)(\\s|$))/i",
                $className)) {
                $contentScore += 25;
            }

            // Look for a special ID
            if (preg_match("/(comment|meta|footer|footnote)/i", $id)) {
                $contentScore -= 50;
            } else if (preg_match(
                "/^(post|hentry|entry[-]?(content|text|body)?|article[-]?(content|text|body)?)$/i",
                $id)) {
                $contentScore += 25;
            }

            // Add a point for the paragraph found
            // Add points for any commas within this paragraph
            if (strlen($paragraph->nodeValue) > 10) {
                $contentScore += strlen($paragraph->nodeValue);
            }

            // 淇濆瓨鐖跺厓绱犵殑鍒ゅ畾寰楀垎
            $parentNode->setAttribute(Service_Copyright_Readability::ATTR_CONTENT_SCORE, $contentScore);

            // 淇濆瓨绔犺妭鐨勭埗鍏冪礌锛屼互渚夸笅娆″揩閫熻幏鍙??
            array_push($this->parentNodes, $parentNode);
        }
       
        $allParagraphs = $this->DOM->getElementsByTagName("cc");
        

        // Study all the paragraphs and find the chunk that has the best score.
        // A score is determined by things like: Number of <p>'s, commas, special classes, etc.
        $i = 0;
        while($paragraph = $allParagraphs->item($i++)) {
            $parentNode   = $paragraph->parentNode;
            $contentScore = intval($parentNode->getAttribute(Service_Copyright_Readability::ATTR_CONTENT_SCORE));
            $className    = $parentNode->getAttribute("class");
            $id           = $parentNode->getAttribute("id");

            // Look for a special classname
            if (preg_match("/(comment|meta|footer|footnote)/i", $className)) {
                $contentScore -= 50;
            } else if(preg_match(
                "/((^|\\s)(post|hentry|entry[-]?(content|text|body)?|article[-]?(content|text|body)?)(\\s|$))/i",
                $className)) {
                $contentScore += 25;
            }

            // Look for a special ID
            if (preg_match("/(comment|meta|footer|footnote)/i", $id)) {
                $contentScore -= 50;
            } else if (preg_match(
                "/^(post|hentry|entry[-]?(content|text|body)?|article[-]?(content|text|body)?)$/i",
                $id)) {
                $contentScore += 25;
            }

            // Add a point for the paragraph found
            // Add points for any commas within this paragraph
            if (strlen($paragraph->nodeValue) > 10) {
                $contentScore += strlen($paragraph->nodeValue);
            }

            // 淇濆瓨鐖跺厓绱犵殑鍒ゅ畾寰楀垎
            $parentNode->setAttribute(Service_Copyright_Readability::ATTR_CONTENT_SCORE, $contentScore);

            // 淇濆瓨绔犺妭鐨勭埗鍏冪礌锛屼互渚夸笅娆″揩閫熻幏鍙??
            array_push($this->parentNodes, $parentNode);
        }
        */
        $topBox = null;
        
        // Assignment from index for performance. 
        //     See http://www.peachpit.com/articles/article.aspx?p=31567&seqNum=5 
        for ($i = 0, $len = sizeof($this->parentNodes); $i < $len; $i++) {
            $parentNode      = $this->parentNodes[$i];
            $contentScore    = intval($parentNode->getAttribute(Service_Copyright_Readability::ATTR_CONTENT_SCORE));
            $orgContentScore = intval($topBox ? $topBox->getAttribute(Service_Copyright_Readability::ATTR_CONTENT_SCORE) : 0);

            if ($contentScore && $contentScore > $orgContentScore) {
            //var_dump($parentNode);
                $topBox = $parentNode;
            }
        }
        // 姝ゆ椂锛??$topBox 搴斾负宸茬粡鍒ゅ畾鍚庣殑椤甸潰鍐呭??逛富鍏冪礌
        //var_dump($topBox);
        return $topBox;
    }


    /**
     * 鑾峰彇 HTML 椤甸潰鏍囬????
     *
     * @return String
     */
    /**
     * @param
     * @return
     */
    public function getTitle() {
        $split_point = ' - ';
        $titleNodes = $this->DOM->getElementsByTagName("title");

        if ($titleNodes->length 
            && $titleNode = $titleNodes->item(0)) {
            // @see http://stackoverflow.com/questions/717328/how-to-explode-string-right-to-left
            $title  = trim($titleNode->nodeValue);
            $result = array_map('strrev', explode($split_point, strrev($title)));
            return sizeof($result) > 1 ? array_pop($result) : $title;
        }

        return null;
    }


    /**
     * Get Leading Image Url
     *
     * @return String
     */
    /**
     * @param
     * @return
     */
    public function getLeadImageUrl($node) {
        $images = $node->getElementsByTagName("img");

        if ($images->length && $leadImage = $images->item(0)) {
            return $leadImage->getAttribute("src");
        }

        return null;
    }


    /**
     * 鑾峰彇椤甸潰鐨勪富瑕佸唴瀹癸紙Service_Copyright_Readability 浠ュ悗鐨勫唴瀹癸級
     *
     * @return Array
     */
    /**
     * @param
     * @return
     */
    public function getContent() {
        if (!$this->DOM) return false;

        // 鑾峰彇椤甸潰鏍囬????
        $ContentTitle = $this->getTitle();

        // 鑾峰彇椤甸潰涓诲唴瀹??
        $ContentBox = $this->getTopBox();
        //Check if we found a suitable top-box.
        if($ContentBox === null)
            //throw new RuntimeException(Service_Copyright_Readability::MESSAGE_CAN_NOT_GET);
            return false;
        
        // 澶嶅埗鍐呭??瑰埌鏂扮殑 DOMDocument
        $Target = new DOMDocument;
        $Target->appendChild($Target->importNode($ContentBox, true));

        // 鍒犻櫎涓嶉渶瑕佺殑鏍囩????
        foreach ($this->junkTags as $tag) {
            $Target = $this->removeJunkTag($Target, $tag);
        }
        
        // 鍒犻櫎涓嶉渶瑕佺殑灞炴????
        foreach ($this->junkAttrs as $attr) {
            $Target = $this->removeJunkAttr($Target, $attr);
        }
        $content = mb_convert_encoding($Target->saveHTML(), Service_Copyright_Readability::DOM_DEFAULT_CHARSET, "HTML-ENTITIES");

        // 澶氫釜鏁版嵁锛屼互鏁扮粍鐨勫舰寮忚繑鍥??
        return Array(
            'lead_image_url' => $this->getLeadImageUrl($Target),
            'word_count' => mb_strlen(strip_tags($content), Service_Copyright_Readability::DOM_DEFAULT_CHARSET),
            'title' => $ContentTitle ? $ContentTitle : null,
            'content' => $content
        );
    }

    /**
     * @param
     * @return
     */
    function __destruct() { }
}
