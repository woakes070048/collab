//<?php

abstract class collab_hook_ipsDispatcherController extends _HOOK_CLASS_
{

	/**
	 * Constructor
	 *
	 * @param	\IPS\Http\Url|NULL	$url		The base URL for this controller or NULL to calculate automatically
	 * @return	void
	 */
	public function __construct( $url=NULL )
	{
		$controllerMap = \IPS\collab\Application::controllerMap();
		
		if ( in_array( $thisClass = get_called_class(), array_keys( $controllerMap ) ) )
		{
			foreach ( $controllerMap[ $thisClass ] as $param => $objClass )
			{
				if ( isset ( \IPS\Request::i()->$param ) )
				{
					try
					{
						$obj = $objClass::load( \IPS\Request::i()->$param );
						\IPS\collab\Application::collabObjStack( $obj );
					}
					catch ( \Exception $e ) {}
				}
			}
		}
		
		return call_user_func_array( 'parent::__construct', func_get_args() );
	}

}