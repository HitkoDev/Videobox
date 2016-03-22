<?php
// no direct access
defined( '_JEXEC' ) or die;
JLoader::discover('Videobox', JPATH_LIBRARIES . '/videobox');
 
class plgSystemVideobox extends JPlugin {
    
    private function getSets(){
        if($this->sets) return $this->sets;
        
        $sets = json_decode($this->params->get('property_sets', '{"default":{"property_set":"default"}}'), true);
        
        $s2 = array();
        foreach($sets as $set => $s){
            if($set == '__keymap') continue;
            $s2[$set] = array(); 
            foreach($s as $key => $value) {
                if(isset($sets['__keymap'][$key])) $s2[$set][ $sets['__keymap'][$key] ] = $value;
            }
        }
        
        $this->sets = $s2;
        return $this->sets;
    }
	
	public function onAfterRender(){
		$app = JFactory::getApplication();
		$document = JFactory::getDocument();
		if($app->isSite() && method_exists($document, 'addCustomTag')){
            $videobox = new VideoboxVideobox(array());
            
            $content = JResponse::getBody();
            preg_match_all("/\{\s*videobox\s*([\@\?]?\s*[^`\}]*([^`\}]*`[^`]*?`)*)\s*\}(.*?){\s*\/\s*videobox\s*\}/ism", $content, $matches, PREG_SET_ORDER);
            
            $sets = $this->getSets();
            
            $instances = array(
                'tags' => array(),
                'properties' => array()
            );
            
            foreach($matches as $match){
                
                $open = trim(strip_tags(html_entity_decode($match[1])));
                $videos = trim(strip_tags(html_entity_decode($match[ count($match) - 1 ])));
                
                $set = '';
                $props = array();
                
                if($open){
                    $l = 0;
                    if($open{$l} == '@'){
                        $l++;   // skip the '@' character
                        
                        preg_match('/[\s\?]/ism', $open, $m, PREG_OFFSET_CAPTURE, $l);  // whitespace or '?' character indicate the end of the property set key 
                        
                        $r = count($m) > 0 ? $m[0][1] : strlen($open);  // if there's no white space or '?' character, set key ends at last character
                        
                        $set = substr($open, $l, $r - $l);
                        
                        $l = $r;    // skip property set name
                    }
                    if($l < strlen($open)){
                        preg_match('/\?\s*/ism', $open, $m, PREG_OFFSET_CAPTURE, $l); // properties start after the '?' character
                        
                        if(count($m) > 0){
                            $l = strlen($m[0][0]) + $m[0][1];
                            
                            preg_match_all("/\&\s*([^=`]*)\s*=\s*`([^`]*)`/ism", $open, $m, PREG_SET_ORDER, $l);    // extranct propertes (&key=`value`)
                            
                            foreach($m as $prop) $props[ $prop[1] ] = $prop[2];
                        }
                    }
                }
                
                $props['videos'] = $videos;
                $instances['tags'][] = $match[0];
                $instances['outputs'][] = $this->generateOutput($videobox, array_merge($sets['default'], isset($sets[$set]) ? $sets[$set] : array(), $props));
                
            }
            
            var_dump($instances);
        }
	}
    
    private function generateOutput($videobox, $scriptProperties){
        $scriptProperties['color'] = trim(str_replace('#', '', $scriptProperties['color']));
        if(strlen($scriptProperties['color']) != 6) $scriptProperties['color'] = '';
        if(!$scriptProperties['color']) $scriptProperties['color'] = '00a645';
        $videos = explode('|,', $scriptProperties['videos']);

        $processors = $videobox->getProcessors();

        $vid = array();
        foreach($videos as $key => $video){
            $video = explode('|', $video);
            $title = '';
            if(isset($video[1])) $title = trim($video[1]);
            $title = $videobox->htmldec($title);
            $title = $videobox->htmlenc($title);
            $video = explode('#', $video[0]);
            $id = trim($video[0]);
            $start = 0;
            $end = 0;
            if(count($video) > 1){
                $video = explode('-', trim($video[count($video) - 1]));
                if(is_numeric(str_replace(':', '', trim($video[0])))){
                    $off = explode (':', trim($video[0]));
                    foreach($off as $off1){
                        $start = $start*60 + $off1;
                    }
                }
                if(is_numeric(str_replace(':', '', trim($video[1])))){
                    $off = explode (':', trim($video[1]));
                    foreach($off as $off1){
                        $end = $end*60 + $off1;
                    }
                }
            }
            $prop = array_merge($scriptProperties, array('id' => $id, 'title' => $title, 'start' => $start, 'end' => $end));
            
            $v = $videobox->getVideo(array('id' => $id, 'title' => $title, 'start' => $start, 'end' => $end));
            if($v) $vid[] = $v;
            
        }
        $videos = $vid;

        if(count($videos) < 1) return;
        $videobox->loadAssets();

        if(!isset($display) || !$display) $display = count($videos) > 1 ? $scriptProperties['multipleDisplay'] : $scriptProperties['singleDisplay'];
        if($display == 'link') $display = 'links';
        if($display == 'links' && $scriptProperties['player'] == 'vbinline') $scriptProperties['player'] = 'videobox';
        $scriptProperties['display'] = $display;
        unset($scriptProperties['multipleDisplay']);
        unset($scriptProperties['singleDisplay']);

        if(count($videos) > 1){
            $tpl = $display == 'links' ? $scriptProperties['linkTpl'] : $scriptProperties['thumbTpl'];
            $start = 0;
            $pagination = '';
            
            if($display == 'gallery'){
                $videobox->gallery++;
                $start = $videobox->getPage();
                $scriptProperties['gallery_number'] = $videobox->gallery;
                $scriptProperties['gallery_page'] = $start;
                $pagination = $videobox->pagination(count($videos), $start, $scriptProperties['perPage']);
                $start = $start*$scriptProperties['perPage'];
            }
            
            if($scriptProperties['player'] == 'vbinline' && ($display == 'gallery' || $display == 'slider')){
                $scriptProperties['pWidth'] = $scriptProperties['tWidth'];
                $scriptProperties['pHeight'] = $scriptProperties['tHeight'];
            }
            
            ksort($scriptProperties);
            $propHash = 'Vb_gallery_' . md5(serialize($scriptProperties));
            $content = $videobox->getCache($propHash);
            if(!$content){
                $n = 0;
                $content = '';
                $props = array('rel' => $scriptProperties['player'], 'pWidth' => $scriptProperties['pWidth'], 'pHeight' => $scriptProperties['pHeight']);
                $filtered = array();
                foreach($videos as $video){
                    $n++;
                    if($start > 0 && $n <= $start) continue;
                    $filtered[] = array(
                        'title' => $video->getTitle(), 
                        'linkText' => $video->getTitle(true), 
                        'link' => $video->getPlayerLink(true), 
                        'thumb' => $videobox->videoThumbnail($video, $display == 'flow'),
                    );
                    if($display == 'gallery' && $n == ($start + $scriptProperties['perPage'])) break;
                }
                $maxR = 0;
                $maxW = $scriptProperties['tWidth'];
                foreach($filtered as $video){
                    $r = $video['thumb'][1]/$video['thumb'][2];
                    if($r > $maxR) $maxR = $r;
                }
                $minR = 0.6;
                foreach($filtered as $video){
                    $r = $video['thumb'][1]/($maxR*$video['thumb'][2]);
                    if($r && $r < $minR) $minR = $r;
                }
                $minR = 1 - log($minR);
                $n = 0;
                foreach($filtered as $video){
                    $v = $videobox->parseTemplate($tpl, array_merge($props, $video, array('thumb' => $video['thumb'][0], 'tWidth' => $video['thumb'][1], 'tHeight' => $video['thumb'][2])));
                    switch($display){
                        case 'links':
                            $v = ($n == 0 ? '' : $scriptProperties['delimiter']) . $v;
                            break;
                        case 'slider':
                            $r = $video['thumb'][1]/($maxR*$video['thumb'][2]);
                            $b = 0.25*$r*$maxW*$minR;
                            $v = $videobox->parseTemplate($scriptProperties['sliderItemTpl'], array_merge($scriptProperties, array('content' => $v, 'ratio' => $r, 'basis' => $b)));
                            break;
                        default:
                            $r = $video['thumb'][1]/($maxR*$video['thumb'][2]);
                            $b = 0.25*$r*$maxW*$minR;
                            $v = $videobox->parseTemplate($scriptProperties['galleryItemTpl'], array_merge($scriptProperties, array('content' => $v, 'ratio' => $r, 'basis' => $b)));
                            break;
                    }
                    $n++;
                    $content .= $v;
                }
                $b = 0.25*$maxW*$minR;
                if($display == 'gallery') for(; $n < $scriptProperties['perPage']; $n++){
                    $v = $videobox->parseTemplate($scriptProperties['galleryItemTpl'], array('ratio' => 1, 'basis' => $b));
                    $content .= $v;
                }
                $videobox->setCache($propHash, $content);
            }
            switch($display){
                case 'links':
                    return $content;
                case 'slider':
                    return $videobox->parseTemplate($scriptProperties['sliderTpl'], array_merge($scriptProperties, array('content' => $content, 'basis' => $scriptProperties['tWidth']/2)));
                default:
                    return $videobox->parseTemplate($scriptProperties['galleryTpl'], array_merge($scriptProperties, array('content' => $content, 'pagination' => $pagination)));
            }
        } else {
            $autoPlay = isset($autoPlay) && $autoPlay && $display == 'player' && (!isset($videobox->autoPlay) || !$videobox->autoPlay);
            $scriptProperties['autoPlay'] = $autoPlay;
            if($autoPlay) $videobox->autoPlay = true;
            ksort($scriptProperties);
            $propHash = 'Vb_video_' . md5(serialize($scriptProperties));
            $data = $videobox->getCache($propHash);
            if($data) return $data;
            $video = $videos[0];
            $props = array_merge(array('rel' => $scriptProperties['player'], 'pWidth' => $scriptProperties['pWidth'], 'pHeight' => $scriptProperties['pHeight'], 'tWidth' => $scriptProperties['tWidth'], 'tHeight' => $scriptProperties['tHeight']), array('title' => $video->getTitle(), 'link' => $video->getPlayerLink($display != 'player' || $autoPlay), 'ratio' => (100*$scriptProperties['pHeight']/$scriptProperties['pWidth'])));
            switch($display){
                case 'links':
                    $props['linkText'] = isset($linkText) ? trim($linkText) : $video->getTitle(true);
                    $v = $videobox->parseTemplate($scriptProperties['linkTpl'], $props);
                    break;
                case 'box':
                    $thumb = $videobox->videoThumbnail($video);
                    $v = $videobox->parseTemplate($scriptProperties['boxTpl'], array_merge($scriptProperties, $props, array('thumb' => $thumb[0], 'tWidth' => $thumb[1], 'tHeight' => $thumb[2])));
                    break;
                default:
                    $v = $videobox->parseTemplate($scriptProperties['playerTpl'], $props);
                    break;
            }
            $videobox->setCache($propHash, $v);
            return $v;
        }
    }
    
}