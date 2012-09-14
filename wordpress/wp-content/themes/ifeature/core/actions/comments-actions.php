<?php

/**
* Exit if file is directly accessed. 
*/ 
if ( !defined('ABSPATH')) exit;

/**
* Comments actions used by the CyberChimps Synapse Core Framework
*
* Author: Tyler Cunningham
* Copyright: © 2011
* {@link http://cyberchimps.com/ CyberChimps LLC}
*
* Released under the terms of the GNU General Public License.
* You should have received a copy of the GNU General Public License,
* along with this software. In the main directory, see: /licensing/
* If not, see: {@link http://www.gnu.org/licenses/}.
*
* @package Synapse
* @since 1.0
*/

/**
* Synapse comments actions
*/
add_action( 'synapse_comments', 'synapse_comments_password_required' );
add_action( 'synapse_comments', 'synapse_comments_loop' );

/**
* Checks if password is required to comment, sets a filter for text that displays.
*
* @since 1.0
*/
function synapse_comments_password_required() {
	
	global $post;
	
	$password_text = apply_filters( 'synapse_password_required_text', 'This post is password protected. Enter the password to view comments.');
	if ( post_password_required() ) { 
		printf( $password_text ); 
		return;
	}
}

/**
* Runs through the comments "loop"
*
* @since 1.0
*/
function synapse_comments_loop() { 
	global $post; ?>
<?php if ( have_comments() ) : ?>
	<div class="comments_container">
		<h2 class="commentsh2"><?php comments_number( __('No Responses', 'core' ), __( 'One Response', 'core' ), __('% Responses', 'core' ));?></h2>

		<div class="navigation">
			<div class="next-posts"><?php previous_comments_link() ?></div>
			<div class="prev-posts"><?php next_comments_link() ?></div>
		</div>
    <div class="clear"></div>

		<ol class="commentlist">
			<?php wp_list_comments('callback=synapse_comment'); ?>
		</ol>

		<div class="navigation">
			<div class="next-posts"><?php previous_comments_link() ?></div>
			<div class="prev-posts"><?php next_comments_link() ?></div>
		</div>
    <div class="clear"></div>
		
	</div><!--end comments_container-->
	
 <?php else : // this is displayed if there are no comments so far ?>

	<?php if ( comments_open() ) : ?>
		<!-- If comments are open, but there are no comments. -->

	 <?php else : // comments are closed ?>

	<?php endif; ?>
	
<?php endif; ?>

<?php if ( comments_open() ) : ?>

<div class="comments_container">

<div id="respond">

	<div class="cancel-comment-reply">
		<?php cancel_comment_reply_link(); ?>
	</div>

	<?php if ( get_option('comment_registration') && !is_user_logged_in() ) : ?>
		<br /><p><?php echo __( 'You must be', 'core' ); ?><a href="<?php echo wp_login_url( get_permalink() ); ?>"> <?php echo __( 'logged in', 'core' ); ?></a> <?php echo __('to post a comment.', 'core' ); ?></p>
	<?php else : ?>
	
	<?php comment_form(); ?>
	
		<?php endif; ?>
		
		<!--<p>You can use these tags: <code><?php echo allowed_tags(); ?></code></p>-->	
		
	</form>
	
</div>

</div><!--end comments_container-->

	<?php endif; // If registration required and not logged in ?>

<?php }

/**
* End
*/

?>