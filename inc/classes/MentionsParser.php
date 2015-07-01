<?php
class MentionsParser
{

    /**
     * Mentions parser
     * By: fetch404
     * Date: 7/1/2015
     * License: MIT
	 *
	 * Modified by Samerton for NamelessMC
     */

	 
	/*
	 *  Private variable
	 */
	private $_db;
	 
    /**
     * Create a new instance of MentionsParser.
     *
     */
    public function __construct()
    {
		// Initialise database connection
		$this->_db = DB::getInstance();
    }

    /**
     * Parse the given HTML to include @username tags.
     *
     * @param string $value
     * @return string
     */
    public function parse($value = '', $topic_id = null, $post_id = null)
    {
        if (preg_match_all("/\@([A-Za-z0-9\-_!\.\s]+)/", $value, $matches))
        {
            $matches = $matches[1];
            foreach($matches as $possible_username)
            {
                $user = null;
                while((strlen($possible_username) > 0) && !$user)
                {
					$user = $this->_db->get('users', array('username', '=', $possible_username));
					$user = $user->first();
                    if ($user)
                    {
                        $value = preg_replace("/".preg_quote("@{$possible_username}", "/")."/", "<a href=\"/profile/{$possible_username}\">@{$possible_username}</a>", $value);
                        
						// Send private message to user
						$this->_db->insert('private_messages', array(
							'author_id' => 0, // 0 is system
							'title' => 'Notification',
							'content' => 'You have been tagged in a post. Click <a href="/forum/view_topic/?tid=' . $topic_id . '&amp;pid=' . $post_id . '">here</a> to view.',
							'sent_date' => date('Y-m-d H:i:s')
						));
						
						$pm_id = $this->_db->lastid();
						
						$this->_db->insert('private_messages_users', array(
							'pm_id' => $pm_id,
							'user_id' => $user->id
						));
						
						break;
                    }

                    // chop last word off of it
                    $new_possible_username = preg_replace("/([^A-Za-z0-9]{1}|[A-Za-z0-9]+)$/", "", $possible_username);
                    if ($new_possible_username !== $possible_username)
                    {
                        $possible_username = $new_possible_username;
                    }
                    else
                    {
                        break;
                    }
                }
            }
        }

        return $value;
    }
}