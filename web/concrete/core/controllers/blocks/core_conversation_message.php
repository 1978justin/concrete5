<?
defined('C5_EXECUTE') or die("Access Denied.");
/**
 * The controller for the conversation message block. This block is used to display conversation messages in a page.
 *
 * @package Blocks
 * @subpackage Conversation
 * @author Andrew Embler <andrew@concrete5.org>
 * @copyright  Copyright (c) 2003-2013 Concrete5. (http://www.concrete5.org)
 * @license    http://www.concrete5.org/license/     MIT License
 *
 */
	class Concrete5_Controller_Block_CoreConversationMessage extends BlockController {

		protected $btCacheBlockRecord = true;
		protected $btTable = 'btCoreConversationMessage';

		public function getBlockTypeDescription() {
			return t("Places a conversation message into a page.");
		}
		
		public function getBlockTypeName() {
			return t("Conversation Message");
		}

		public function composer() {
			$html = Loader::helper('html');
			$this->addHeaderItem($html->css('ccm.conversations.css'));
			$this->addFooterItem($html->javascript('ccm.conversations.js'));
			$this->view();
		}

		public function validate_composer($data) {
			$vs = Loader::helper('validation/strings');
			$ve = Loader::helper('validation/error');
			$subject = Loader::helper('security')->sanitizeString($data['cnvMessageSubject']);
			if (!$vs->notempty($subject)) {
				$ve->add(t('Your subject cannot be empty.'));
			}

			if (!$vs->notempty($data['cnvMessageBody'])) {
				$ve->add(t('Your message cannot be empty.'));
			}

			if (Config::get('CONVERSATION_DISALLOW_BANNED_WORDS') && (
				Loader::helper('validation/banned_words')->hasBannedWords($data['cnvMessageSubject']) ||
				Loader::helper('validation/banned_words')->hasBannedWords($data['cnvMessageBody']))) {
				$ve->add(t('Banned words detected.'));	
			}
			return $ve;
		}

		public function save($args) {
			$db = Loader::db();
			$cnvMessageID = $db->GetOne('select cnvMessageID from btCoreConversationMessage where bID = ?', array($this->bID));
			if (!$cnvMessageID) {
				$message = ConversationMessage::add(false, $args['cnvMessageSubject'], $args['cnvMessageBody']);
				if (!Loader::helper('validation/antispam')->check($args['cnvMessageBody'],'conversation_comment')) {
					$message->flag(ConversationFlagType::getByHandle('spam'));
				} else {
					$message->approve();
				}
				$data = array();
				$data['cnvMessageID'] = $message->getConversationMessageID();
				parent::save($data);
				// update any conversation blocks on that page to have their conversations reflect that this is a base message block.
				// we will then use that to group and show replies and messages in the dashboard
				$b = $this->getBlockObject();
				$c = $b->getBlockCollectionObject();
				$blocks = $c->getBlocks();
				foreach($blocks as $b) {
					if ($b->getBlockTypeHandle() == BLOCK_HANDLE_CONVERSATION) {
						$bi = $b->getController();
						$conversation = $bi->getConversationObject();
						$conversation->setConversationParentMessageID($message->getConversationMessageID());
					}
				}
			}

		}

		public function view() {
			$message = $this->getConversationMessageObject();
			$this->set('message', $message);
		}

		public function getConversationMessageObject() {
			if (!isset($this->message)) {
				$db = Loader::db();
				$cnvMessageID = $db->GetOne('select cnvMessageID from btCoreConversationMessage where bID = ?', array($this->bID));
				$this->message = ConversationMessage::getByID($cnvMessageID);
			}
			return $this->message;
		}

		public function getComposerControlPageNameValue() {
			$message = $this->getConversationMessageObject();
			return $message->getConversationMessageSubject();
		}
		
	}