<?php
function widget_qf_navs( &$setting, &$system )
{
				$output =& $system->loadmodel( "system/frontend" );
				$baseurl = $system->base_url( );
				$rewrite = intval( $system->getconf( "system.seo.emuStatic" ) );
				$result = $comma = "";
				$total = $i = 0;
				$bgimg = $setting['bgimg'] ? "bgimg " : "";
				if ( is_array( $setting['menus'] ) && ( $total = count( $setting['menus'] ) ) )
				{
								foreach ( $setting['menus'] as $m )
								{
												if ( !trim( $m['text'] ) )
												{
												}
												else
												{
																$m['url'] = preg_replace( "/%BASEURL%(\\/*)/", "./", $m['url'] );
																@preg_match( "/^%(_?[\\w]+)%(.+)?/i", @$m['url'], $target );
																if ( $target )
																{
																				$m['url'] = $target[2];
																				$target = $m['url'] ? " target=\"".$target[1]."\"" : "";
																}
																else
																{
																				$target = "";
																}
																if ( substr_count( $m['url'], "/" ) || substr_count( $m['url'], "{BASEURL}" ) )
																{
																				$m['url'] = " href=\"".preg_replace( "/^(\\.\\/|\\{BASEURL\\})*(\\?)?/es", "('\\1'?'{$baseurl}':'').({$rewrite}?'':'\\2')", $m['url'] )."\"";
																}
																else
																{
																				$m['url'] = $m['url'] ? " href=\"".$baseurl.( $rewrite ? "" : "?" ).$m['url']."\"" : "";
																}
																$class = $bgimg;
																$class .= !$i ? "first " : "";
																$class .= $m['classname'] ? $m['classname']." " : "";
																$class .= $total - 1 == $i ? "last " : "";
																$class = $class ? " class=\"".trim( $class )."\"" : "";
																$comma = $setting['comma'] && !preg_match( "/hide/i", $m['classname'] ) ? $comma : "";
																$result .= $comma."<li".$class."><a".$m['url'].$target."><span>".$m['text']."</span></a></li>";
																$comma = $setting['comma'] ? "<li class=\"comma\"><span>".$setting['comma']."</span></li>" : "";
																++$i;
												}
								}
				}
				$tag = $setting['bold'] ? "strong" : "span";
				$fronttips = trim( $setting['fronttips'] ) ? "<li class=\"front".( $i ? "" : " last" )."\"><".$tag.">".$setting['fronttips']."</".$tag."></li>" : "";
				$result = !$fronttips && !$result ? "<li class=\"".$bgimg."last\"><a href=\"".$baseurl."\">清风设计</a></li>" : $fronttips.$result;
				return "<ul class=\"qfnavs\">".$result."</ul>";
}

?>
