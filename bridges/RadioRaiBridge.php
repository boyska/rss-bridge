<?php
class RadioRaiBridge extends BridgeAbstract {

	const MAINTAINER = 'boyska';
	const NAME = 'Radio Rai';
	const URI = 'https://www.raiplayradio.it';
	const CACHE_TIMEOUT = 1; // 10min
	const DESCRIPTION = 'Segui le trasmissioni radio rai con feed/podcast valido';
	const PARAMETERS = array( array(
		'txname' => array(
			'name' => 'txname',
			'required' => true
		)
	));

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
            $item['enclosures'] = [ $audiourl ];
            $item['url'] = $this::URI . $episode->getAttribute('data-href');

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
