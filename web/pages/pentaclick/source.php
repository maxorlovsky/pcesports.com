<?php

class pentaclick extends system
{
    public $team;
    
	public function __construct($params = array()) {
        
	}
    
    public function getStaff() {
        $teamIds = array(
            'max'   => 1,
            'serge' => 44,
            'anya'  => 112,
            'dantey'=> 122,
        );

        if (_cfg('env') != 'prod') {
            $teamIds = array(
                'max'   => 1,
            );
        }

        $where = implode(' OR `id` = ', $teamIds);
        $where = '`id` = '.$where;

        $rows = Db::fetchRows('SELECT `id`, `name`, `avatar` FROM `users` WHERE '.$where);
        
        $this->team = array(
            $teamIds['max'] => array(
                'role' => 'I\'m basically doing everything',
                'socials' => array(
                    'fb' => 'https://www.facebook.com/MaxOrlovsky',
                    'tw' => 'https://twitter.com/PCEMaxtream',
                    'tv' => 'http://www.twitch.tv/pentaclick_tv',
                )
            ),
            $teamIds['dantey'] => array(
                'role' => 'Hearthstone Mastermind',
                'socials' => array(
                    'fb' => 'https://www.facebook.com/andris.krumins.5',
                    'tw' => 'https://twitter.com/Andris_K',
                )
            ),
            $teamIds['serge'] => array(
                'role' => 'Helping with stuff',
                'socials' => array(
                    'fb' => 'https://www.facebook.com/grobovsky',
                )
            ),
            $teamIds['anya'] => array(
                'role' => 'Creative Generator',
                'socials' => array(
                    'fb' => 'https://www.facebook.com/anya.orlovsky',
                )
            ),
        );
        
        if ($rows) {
            foreach($rows as $v) {
                $this->team[$v->id]['name'] = $v->name;
                $this->team[$v->id]['avatar'] = $v->avatar;
            }
        }

        include_once _cfg('pages').'/'.get_class().'/index.tpl';
    }
    
	
	public function showTemplate() {
        $this->getStaff();
	}
}