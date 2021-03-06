<?php
/**
 * YiiMailMessage class file.
 *
 * @author Jonah Turnquist <poppitypop@gmail.com>
 * @link https://code.google.com/p/yii-mail/
 * @package Yii-Mail
 */

/**
 * Any requests to set or get attributes or call methods on this class that are
 * not found in that class are redirected to the {@link Swift_Mime_Message}
 * object.
 *
 * This means you need to look at the Swift Mailer documentation to see what
 * methods are availiable for this class.  There are a <b>lot</b> of methods,
 * more than I wish to document.  Any methods availiable in
 * {@link Swift_Mime_Message} are availiable here.
 *
 * Documentation for the most important methods can be found at
 * {@link http://swiftmailer.org/docs/messages}
 *
 * The YiiMailMessage component also allows using a shorthand for methods in
 * {@link Swift_Mime_Message} that start with set* or get*
 * For instance, instead of calling $message->setFrom('...') you can use
 * $message->from = '...'.
 *
 * Here are a few methods to get you started:
 * <ul>
 *     <li>setSubject('Your subject')</li>
 *     <li>setFrom(array('john@doe.com' => 'John Doe'))</li>
 *     <li>setTo(array('receiver@domain.org', 'other@domain.org' => 'Name'))</li>
 *     <li>attach(Swift_Attachment::fromPath('my-document.pdf'))</li>
 * </ul>
 *
 * @property $from От кого (email)
 * @property $to Кому (email)
 * @property $subject Тема письма
 *
 */
class YiiMailMessage extends CComponent {

	/**
	 * @var string the view to use for rendering the body, null if no view is
	 * used.  An extra variable $mail will be passed to the view .which you may
	 * use to set e.g. the email subject from within the view
	 */
	public $view;

	/**
	 * @var Swift_Mime_Message
	 */
	public $message;

	/**
	 * Any requests to set or get attributes or call methods on this class that
	 * are not found are redirected to the {@link Swift_Mime_Message} object.
	 * @param string $name attribute name
	 * @throws CException|Exception
	 * @return mixed
	 */
	public function __get($name) {
		try {
			return parent::__get($name);
		} catch (CException $e) {
			$getter = 'get' . $name;
			if (method_exists($this->message, $getter)) {
				return $this->message->$getter();
			} else {
				throw $e;
			}
		}
	}

	/**
	 * Any requests to set or get attributes or call methods on this class that
	 * are not found are redirected to the {@link Swift_Mime_Message} object.
	 * @param string $name
	 * @param mixed $value
	 * @throws CException|Exception
	 * @internal param \the $string attribute name
	 * @return mixed
	 */
	public function __set($name, $value) {
		try {
			return parent::__set($name, $value);
		} catch (CException $e) {
			$setter = 'set' . $name;
			if (method_exists($this->message, $setter)) {
				$this->message->$setter($value);
			} else {
				throw $e;
			}
		}
	}

	/**
	 * Any requests to set or get attributes or call methods on this class that
	 * are not found are redirected to the {@link Swift_Mime_Message} object.
	 * @param string $name
	 * @param array $parameters
	 * @throws CException|Exception
	 * @internal param \the $string method name
	 * @return mixed
	 */
	public function __call($name, $parameters) {
		try {
			return parent::__call($name, $parameters);
		} catch (CException $e) {
			if (method_exists($this->message, $name)) {
				return call_user_func_array([$this->message, $name], $parameters);
			} else {
				throw $e;
			}
		}
	}

	/**
	 * You may optionally set some message info using the paramaters of this
	 * constructor.
	 * Use {@link view} and {@link setBody()} for more control.
	 *
	 * @param string $subject
	 * @param string $body
	 * @param string $contentType
	 * @param string $charset
	 * @return \YiiMailMessage
	 */
	public function __construct($subject = null, $body = null, $contentType = null, $charset = null) {
		Yii::app()->mail->registerScripts();
		$this->message = Swift_Message::newInstance($subject, $body, $contentType, $charset);
	}

	/**
	 * Set the body of this entity, either as a string, or array of view
	 * variables if a view is set, or as an instance of
	 * {@link Swift_OutputByteStream}.
	 *
	 * @param mixed $body body of the message. If a $this->view is set and this is a string, this is passed to the view as $body. If $this->view is set and this is an array, the array values are passed to the view like in the controller render() method
	 * @param string $contentType type optional. For html, set to 'html/text'
	 * @param string $charset optional
	 * @return
	 */
	public function setBody($body = '', $contentType = null, $charset = null) {
		if ($this->view !== null) {
			if (!is_array($body)) {
				$body = ['body' => $body];
			}

			// if Yii::app()->controller doesn't exist create a dummy
			// controller to render the view (needed in the console app)
			if (isset(Yii::app()->controller)) {
				$controller = Yii::app()->controller;
			} else {
				$controller = new CController('YiiMail');
			}

			$viewData = array_merge($body, ['mail' => $this]);

			if (/** @var IViewRenderer $viewRenderer */ $viewRenderer = Yii::app()->getComponent('viewRenderer')) {
				$viewPath = Yii::getPathOfAlias(Yii::app()->mail->viewPath . '.' . $this->view) . $viewRenderer->fileExtension;
				$body = $viewRenderer->renderFile($controller, $viewPath, $viewData, true);
			} else {
				$viewPath = Yii::getPathOfAlias(Yii::app()->mail->viewPath . '.' . $this->view) . '.php';
				$viewPath = Yii::app()->findLocalizedFile($viewPath);
				$body     = $controller->renderInternal($viewPath, $viewData, true);
			}
		}
		return $this->message->setBody($body, $contentType, $charset);
	}
}
