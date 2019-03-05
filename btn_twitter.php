  
<?php  

/*
Plugin Name: btn_wordpress
Plugin URI: www.rflabs-online.com/wordpress.com
Description: Esto es un plugin wordpress que te permite ver los primeros 20 tweets dependidendo de la etiqueta que se usa
Version: 1.0
Author: Jhonatan Romero
Author URI: www.rflabs-online.com/wordpress.com
License: GPL2
*/

add_action( 'add_meta_boxes', 'enter_create_metabox' );
  function enter_create_metabox () {
  add_meta_box( 'enter-metabox1', 'primeros 20 tweets', 'enter_create_metabox_function', 'post', 'normal', 'high' );
}


function enter_create_metabox_function(){
  define('BASE', dirname(__FILE__));
	define('APP_CONSUMER_KEY', 'mx8hytE5LEajMcSbiVKp8rA8a');
	define('APP_CONSUMER_SECRET', 'qQeSWFKNJ84Es9WPw6ozoBNZYn56rVey4T4mk10RTWhQQ3d9BT');
	define('ACCESS_TOKEN', '348388496-QBq3zPTNfAtPcntGwuxEsCiyOEBlh5QAFKpqtlzh');
	define('ACCESS_TOKEN_SECRET', 'IChujRClDaig7bLoYbfT341squ824sSqqh0cVLM4gDlHq');
	define('CACHE_ENABLED', false);


	function tweet_text($text) {
		$text =  preg_replace("/(http:\/\/[^\s]+)/", '<a href="$1" class="tweet-link" target="_blank">$1</a>', $text);
		$text =  preg_replace("/(^|\W)@([A-Za-z0-9_]+)/", '$1<a href="http://twitter.com/$2" class="tweet-mention" target="_blank">@$2</a>', $text);
		$text =  preg_replace("/(^|\W)#([^\s]+)/", '$1<a href="?q=%23$2" class="tweet-hash" target="_blank">#$2</a>', $text);
		return $text;
	}

	if( isset($_GET['q']) && !empty($_GET['q']) ) {
		$results = null;
		if( CACHE_ENABLED ) {
			include 'inc/Cache.php';
			Cache::configure(array(
				'cache_dir' => BASE . '/cache',
				'expires' => 1 // hora
			));
			$results = Cache::get($_GET['q']);
		}
    
		if( ! $results ) {
			include 'inc/twitteroauth.php';
			$twitteroauth = new TwitterOAuth(APP_CONSUMER_KEY, APP_CONSUMER_SECRET, ACCESS_TOKEN, ACCESS_TOKEN_SECRET);
			$results = $twitteroauth->get('search/tweets', array(
				'q' => $_GET['q'], 
				'lang' => 'es',
				'count' => 20,
				'include_entities' => false,
			));
			if( CACHE_ENABLED ) {
				Cache::put($_GET['q'], $results);
			}
		}


	}
  
  
      echo '<form class="main-form" action="" method="GET">
          <input type="text" id="q" name="q" value="'.$_GET['q'].'" placeholder="tag de busqueda" required>
          <button type="submit"><span class="assistive-text">Buscar</span></button>
        </form>';
        
  ?>

        
  			<?php if(isset($results)): ?>
  					<?php if(count($results->statuses)): ?>
  						<?php foreach ($results->statuses as $tweet): ?>
  									<img class="tweet-user-image" src="<?php echo $tweet->user->profile_image_url ?>" alt="@<?php echo $tweet->user->screen_name ?>">
  									<a class="tweet-user-link" href="http://twitter.com/<?php echo $tweet->user->screen_name ?>" title="@<?php echo $tweet->user->screen_name ?>"><?php echo $tweet->user->name ?></a>
  								<p class="tweet-text"><?php echo tweet_text($tweet->text); ?></p>
  						<?php endforeach; ?>
  					<?php else: ?>
  						<div class="error-message">No se ha encontrado ningún tweet</div>
  					<?php endif; ?>
  			<?php endif; ?>

  
  <?php
}
