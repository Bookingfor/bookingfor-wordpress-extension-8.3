<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
					if(!empty( $tags)){
						?><div class="bfi-facilities"><?php 
						
						foreach ( $tags as $gr ) {

							?>
							<div class="bfi-group-tags bfi-group-tags-<?php echo $gr->TagGroupId?> bfi-hideonextra" >
							<h3 class="bfi-description-sub-header">
								<?php 
									$strHtml = "";
									if (!empty($gr->ImageUrl)) {
										$strHtml .= "<img src='" . BFCHelper::getImageUrlResized('tag',$gr->ImageUrl, 'tag24') . "' alt='" . $gr->Name . "' title='" . $gr->Name . "' />";
										
									}else {
									   if (!empty($gr->IconSrc)) {
												$currType = explode(';',$gr->IconType);
												switch ($currType[0]) {
													case "fontawesome5":
														$strHtml .= "<i class='" . $gr->IconSrc . "'></i> ";
														break;
													case "fontawesome4":
													default:
														$strHtml .= "<i class='fa " . $gr->IconSrc . "'></i> ";
												}

									   }else{
										   $strHtml .= "";
									   }
									
									}
									$strHtml .= $gr->Name;
									echo $strHtml;
//									echo $gr->Name; 
								?>:
							</h3>
							<?php
							switch ($gr->LayoutType ) {
								case 0:
									$tagsList = implode(' ',array_filter(array_map(function ($i) { 
											$strHtml = '<span style="color:' . $i->SubGroupColor. '">';
												if (!empty($i->ImageUrl)) {
													$strHtml .= "<img src='" . BFCHelper::getImageUrlResized('tag',$i->ImageUrl, 'tag24') . "' alt='" . $i->Name . "' title='" . $i->Name . "' />";
													
												}else {
												   if (!empty($i->IconSrc)) {
															$currType = explode(';',$i->IconType);
															switch ($currType[0]) {
																case "fontawesome5":
																	$strHtml .= "<i class='" . $i->IconSrc . "' style='color:" . $i->SubGroupColor. "'></i> ";
																	break;
																case "fontawesome4":
																default:
																	$strHtml .= "<i class='fa " . $i->IconSrc . "' style='color:" . $i->SubGroupColor. "' ></i> ";
															}

												   }else{
													   $strHtml .= "";
												   }
												
												}
											$strHtml .= $i->Name . '</span>';
											return $strHtml;
										}, $gr->Tags)));
//									$tagsList = implode(' ',array_filter(array_map(function ($i) { return '<span style="color:' . $i->SubGroupColor. '">' . (!empty($i->ImageUrl) ? "<img src='" . BFCHelper::getImageUrlResized('tag',$i->ImageUrl, 'tag24') . "' alt='" . $i->Name . "' title='" . $i->Name . "' />" : (!empty($i->IconSrc) ? "<i class='fa " . $i->IconSrc . "'></i> ":""))  . $i->Name . '</span>'; }, $gr->Tags)));
									?>
									<div class="bfi-facility-list"><?php echo $tagsList ?></div>		
									<?php 
									break;
								case 1:
									foreach($gr->subgrsHighlight  as $subgr) {
											$tagsList = implode(' ',array_filter(array_map(function ($i) {
												$strHtml = '<span class="bfi-tag-highlight">';
													if (!empty($i->ImageUrl)) {
														$strHtml .= "<img src='" . BFCHelper::getImageUrlResized('tag',$i->ImageUrl, 'tag24') . "' alt='" . $i->Name . "' title='" . $i->Name . "' />";
														
													}else {
													   if (!empty($i->IconSrc)) {
																$currType = explode(';',$i->IconType);
																switch ($currType[0]) {
																	case "fontawesome5":
																		$strHtml .= "<i class='" . $i->IconSrc . "'></i> ";
																		break;
																	case "fontawesome4":
																	default:
																		$strHtml .= "<i class='fa " . $i->IconSrc . "'></i> ";
																}

													   }else{
														   $strHtml .= "";
													   }
													
													}
												$strHtml .= $i->Name . '</span>';
												return $strHtml;
													
													//return '<span><i class="fa fa-check"></i>&nbsp;' . $i->Name . '</span>'; 
												}, $subgr)));
											?>
											<div class="bfi-tags-highlight"><?php echo $tagsList ?></div>		
											<?php 

									}
									if (!empty($gr->subgrs )) {
										?>
										<div class="bfi-list-tags-container">
										<?php 
										
									}
									foreach($gr->subgrs  as $subgr) {
										?>
										<div class="bfi-list-tags">
											<h5>
												<?php																						
													$strHtml = "";
													if (!empty($subgr[0]->SubGroupImageSrc)) {
														$strHtml .= "<img src='" . BFCHelper::getSubGroupImageSrcResized('tag',$subgr[0]->SubGroupImageSrc, 'tag24') . "' alt='" . $subgr[0]->Name . "' title='" . $subgr[0]->Name . "' />";
														
													}else {
													   if (!empty($subgr[0]->SubGroupIconSrc)) {
																$currType = explode(';',$subgr[0]->SubGroupIconType);
																switch ($currType[0]) {
																	case "fontawesome5":
																		$strHtml .= "<i class='" . $subgr[0]->SubGroupIconSrc . "'></i> ";
																		break;
																	case "fontawesome4":
																	default:
																		$strHtml .= "<i class='fa " . $subgr[0]->SubGroupIconSrc . "'></i> ";
																}

													   }else{
														   $strHtml .= "";
													   }
													
													}
													$strHtml .= $subgr[0]->SubGroupName;
													echo $strHtml;
													//echo $subgr[0]->SubGroupName;

													if (!empty($subgr[0]->SubGroupInEvidence))
													{
														echo '<span class="bfi-tag-inevidence">' . __('in evidence', 'bfi') . '</span>';
													}
												?>
											</h5>
											<?php
												foreach ($subgr as $tg ) {
							?>
													<div class="bfi-tags" >
														<?php 
														switch ($tg->TagValueType ) {
															case 1:
																switch ($tg->TagValue ) {
																	case '-1':
																		echo '<i class="fa fa-check"></i> '. $tg->Name . ' <span class="bfi-tag-excluding">' . __('excluding', 'bfi') . '</span>';
																	    break;
																	case '0':
																		echo '<i class="fa fa-check"></i> '. $tg->Name . ' <span class="bfi-tag-included">' . __('included', 'bfi') . '</span>';
																	    break;
																	case '1':
																		echo '<span class="bfi-tag-free">' .__('Free!', 'bfi') . '</span> ' . $tg->Name ;
																	    break;
																	case '2':
																		echo '<i class="fa fa-check"></i> '. $tg->Name . ' <span class="bfi-tag-additional">' . __('additional charge', 'bfi') . '</span>';
																	    break;
																	default:
																		echo '<i class="fa fa-check"></i> ' . $tg->Name ;
																	    break;
																}
//																if ($tg->TagValue == "1") {
//																	echo '<span class="bfi-tag-free">' .__('Free!', 'bfi') . '</span> ' . $tg->Name ;
//																}else if ($tg->TagValue == "2") {
//																	echo '<i class="fa fa-check"></i> '. $tg->Name . ' <span class="bfi-tag-additional">' . __('additional charge', 'bfi') . '</span>';
//																}
																break;
															default:
																echo '<i class="fa fa-check"></i> ' . $tg->Name ;
																
														}
														?>
													</div>					    
													<?php 
												}
										?>
										</div>					    
										<?php 
									}
									if (!empty($gr->subgrs )) {
										?>
										</div>
										<?php 
									}
								break;
							}
							?>
							</div>					    
							<?php 
						}
						?></div><?php 
					}
?>