<?php
class AutoPodcasterBridge extends FeedExpander {
    const MAINTAINER='boyska';
    const NAME='Auto Podcaster';
    const URI = '';
    const CACHE_TIMEOUT = 0;
    const DESCRIPTION='Crea un podcast multimediale a partire da un feed normale';
    const PARAMETERS = array('url' => array(
        'url' => array(
            'name' => 'URL',
            'required' => true
    )));

    private function archiveIsAudioFormat($formatString) {
        return strpos($formatString, 'MP3') !== false ||
            strpos($formatString, 'Ogg') === 0;
    }

	protected function parseItem($newItem){
		$item = parent::parseItem($newItem);

		$dom = getSimpleHTMLDOMCached($item['uri']);
        $audios = [];

        /* 1st extraction method: by "audio" tag */
        foreach($dom->find('audio') as $audioEl) {
            $sources = [];
            if($audioEl->src !== false) {
                $sources[] = $audioEl->src;
            }
            foreach($audioEl->find('source') as $sourceEl) {
                $sources[] = $sourceEl->src;
            }
            if($sources) {
                $audios[$sources[0]] = ['sources' => [$sources]];
            }
        }

        /* 2nd extraction method: by "iframe" tag */
        foreach($dom->find('iframe') as $iframeEl) {
            if(strpos($iframeEl->src, "https://archive.org/embed/") === 0) {
                $listURL = preg_replace("/\/embed\//", "/details/", $iframeEl->src, 1) . "?output=json";
                $baseURL = preg_replace("/\/embed\//", "/download/", $iframeEl->src, 1);
                $list = json_decode(file_get_contents($listURL));
                $audios = [];
                foreach($list->files as $name =>$data) {
                    if($data->source === 'original' &&
                        $this->archiveIsAudioFormat($data->format)) {
                        $audios[$baseURL . $name] = ['sources' => [$baseURL . $name]];
                    }
                }
                foreach($list->files as $name =>$data) {
                    if($data->source === 'derivative' &&
                        $this->archiveIsAudioFormat($data->format) &&
                        isset($audios[$baseURL . "/" . $data->original])) {
                        $audios[$baseURL . "/" . $data->original]['sources'][] = $baseURL . $name;
                    }
                }
            }
        }



        if(count($audios) === 0) {
            return null;
        }
        $item['enclosures'] = array_values($audios);
        $item['enclosures'] = [];
        foreach(array_values($audios) as $audio) {
            $item['enclosures'][] = $audio['sources'][0];
        }
        return $item;
	}
	public function collectData(){
		if($this->getInput('url') && substr($this->getInput('url'), 0, strlen('http')) !== 'http') {
			// just in case someone find a way to access local files by playing with the url
			returnClientError('The url parameter must either refer to http or https protocol.');
		}
        $this->collectExpandableDatas($this->getURI());
	}
	public function getName(){
		if(!is_null($this->getInput('url'))) {
			return self::NAME . ' : ' . $this->getInput('url');
		}

		return parent::getName();
	}
	public function getURI(){
		return $this->getInput('url');
    }

}
