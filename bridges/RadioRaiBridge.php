<?php
class RadioRaiBridge extends BridgeAbstract {

	const MAINTAINER = 'boyska';
	const NAME = 'Radio Rai';
	const URI = 'https://www.raiplayradio.it';
	const CACHE_TIMEOUT = 900; // 15min
	const DESCRIPTION = 'Segui le trasmissioni radio rai con feed/podcast valido';
	const PARAMETERS = array( array(
		'txname' => array(
			'name' => 'txname',
			'required' => true
		)
	));

    private function getFinalURL($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        $ret = curl_exec($ch);
        if($ret === FALSE) {
            return null;
        }
        $redirect = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
        if($redirect === false) return $url;
        return $redirect;
    }

	public function collectData(){
		$html = getSimpleHTMLDOM($this->getURI())
			or returnServerError('No results for this query.');

		foreach($html->find('[data-mediapolis]') as $episode) {
            // var_dump($episode);
            
            $title = $episode->getAttribute('data-title');
            if($title === FALSE) { continue; }
            $audiourl = $episode->getAttribute('data-mediapolis');
            $item = array();
            $item['author'] = $this->getInput('txname');
            $item['title'] = $title;
            $item['content'] = $episode->plaintext;
            $item['enclosures'] = [ $this::getFinalURL($audiourl) ];
            $item['uri'] = $this::URI . $episode->getAttribute('data-href');

            $this->items[] = $item;
		}
	}

	public function getURI(){
        return 'https://www.raiplayradio.it/programmi/' .  $this->getInput('txname') .  '/archivio/puntate/';
	}

	public function getName(){
        if($this->getInput('txname')) {
            return 'Radio Rai - ' . $this->getInput('txname');
        }

		return parent::getName();
	}
}
