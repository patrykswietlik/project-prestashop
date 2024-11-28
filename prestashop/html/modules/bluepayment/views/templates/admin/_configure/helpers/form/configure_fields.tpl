{*
 * BlueMedia_BluePayment extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GNU Lesser General Public License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://www.gnu.org/licenses/lgpl-3.0.en.html
 *
 * @category       BlueMedia
 * @package        BlueMedia_BluePayment
 * @copyright      Copyright (c) 2015-2024
 * @license        https://www.gnu.org/licenses/lgpl-3.0.en.html GNU Lesser General Public License
*}
{block name="input_row"}
	<div class="form-group {$_input.name|lower}{if isset($_input.form_group_class)} {$_input.form_group_class}{/if}"{if $_input.name == 'id_state'} id="contains_states"{if !$contains_states} style="display:none;"{/if}{/if}{if isset($tabs) && isset($_input.tab)} data-tab-id="{$_input.tab}"{/if}>
        {if $_input.type == 'hidden'}
			<input type="hidden" name="{$_input.name}" id="{$_input.name}"
			       value="{$fields_value[$_input.name]|escape:'html':'UTF-8'}"/>
        {elseif $_input.type == 'description'}
			<div class="infoheading_class col-sm-12">
                {assign var=desc_template value=$_input.content}
                {include file="$desc_template"}
			</div>
        {elseif $_input.type == 'infoheading'}
			<div class="section-heading">
				{$_input.label}
			</div>
        {else}
            {block name="label"}
                {if isset($_input.label)}
					<label class="control-label text-left text-lg-right col-xs-12 col-lg-3 {if isset($_input.class)}{$_input.class}{/if} {if isset($_input.required) && $_input.required} required{/if}">
                        {if isset($_input.hint)}
						<span class="label-tooltip"
						      data-toggle="tooltip" data-html="true"
						      title="{if is_array($_input.hint)}
													{foreach $_input.hint as $hint}
														{if is_array($hint)}
															{$hint.text|escape:'html':'UTF-8'}
														{else}
															{$hint|escape:'html':'UTF-8'}
														{/if}
													{/foreach}
												{else}
													{$_input.hint|escape:'html':'UTF-8'}
												{/if}">
										{/if}
                            {$_input.label}
                            {if isset($_input.doc)}
								<span class="doc_class">
													<a target="_blank" href="{$_input.doc}">?</a>
												</span>
                            {/if}
                            {if isset($_input.hint)}
										</span>
                        {/if}

					</label>
                  {if isset($_input.class)}
                    <div class="col-lg-5 p-0">
                        {if isset($_input.image)}
                          <img width="80" class="img-fluid {if isset($_input.class)}{$_input.class}{/if} "
                               src="{$src_img|escape:'html':'UTF-8'}/helpers/{$_input.image|escape:'html':'UTF-8'}"/>
                            {if isset($_input.modal)}
                              <a target="#" data-toggle="modal"
                                 data-target="#{$_input.modal}" style="cursor:pointer">
                                <img width="22" style="margin-left: 6px;"
                                     class="bm-info--small__icon img-fluid"
                                     src="{$src_img|escape:'html':'UTF-8'}/question.png"
                                />
                              </a>
                            {/if}
                        {/if}
                    </div>
                      {include file='./promo-modals.tpl'}
                  {/if}
                {/if}
            {/block}

            {block name="field"}
				<div class="col-xs-12 col-lg-5 {if isset($_input.class)}js-bm-promo-payment{/if}  {if isset($_input.col)}{$_input.col|intval}{else}5{/if} {if !isset($_input.label)}col-lg-offset-3{/if}">
                    {block name="input"}
                        {if $_input.type == 'text'}
                            {if isset($_input.lang) AND $_input.lang}
                                {if $languages|count > 1}
									<div class="form-group">
                                {/if}

                                {foreach $languages as $language}
                                    {if isset($fields_value[$_input.name][$language.id_lang])}
                                        {assign var='value_text' value=$fields_value[$_input.name][$language.id_lang]}
                                    {else}
                                        {assign var='value_text' value=""}
                                    {/if}

                                    {if $languages|count > 1}
										<div class="translatable-field lang-{$language.id_lang}" {if $language.id_lang != $defaultFormLanguage}style="display:none"{/if}>
										<div class="col-lg-9">
                                    {/if}

	                                {if isset($_input.maxchar) || isset($_input.prefix) || isset($_input.suffix)}
										<div class="input-group{if isset($_input.class)} {$_input.class}{/if}">
	                                {/if}
                                    {if isset($_input.maxchar)}
										<span id="{if isset($_input.id)}{$_input.id}_{$language.id_lang}{else}{$_input.name}_{$language.id_lang}{/if}_counter" class="input-group-addon">
											<span class="text-count-down">{$_input.maxchar}</span>
										</span>
                                    {/if}
                                    {if isset($_input.prefix)}
										<span class="input-group-addon">
											{$_input.prefix}
										</span>
                                    {/if}

									<input type="text"
									       id="{if isset($_input.id)}{$_input.id}_{$language.id_lang}{else}{$_input.name}_{$language.id_lang}{/if}"
									       name="{$_input.name}_{$language.id_lang}"
									       class="{if isset($_input.class)}{$_input.class}{/if}"
									       value="{if isset($_input.string_format) && $_input.string_format}{if isset($value_text) && !empty($value_text)}{$value_text|string_format:$_input.string_format|escape:'html':'UTF-8'}{else}{if isset($_input.default_val) && !empty($_input.default_val)}{$_input.default_val|string_format:$_input.string_format|escape:'html':'UTF-8'}{/if}{/if}{else}{if isset($value_text) && !empty($value_text)}{$value_text|escape:'html':'UTF-8'}{else}{if isset($_input.default_val) && !empty($_input.default_val)}{$_input.default_val|escape:'html':'UTF-8'}{/if}{/if}{/if}"
									       onkeyup="if (isArrowKey(event)) return ;updateFriendlyURL();"
                                            {if isset($_input.size)} size="{$_input.size}"{/if}
                                            {if isset($_input.maxchar)} data-maxchar="{$_input.maxchar}"{/if}
                                            {if isset($_input.maxlength)} maxlength="{$_input.maxlength}"{/if}
                                            {if isset($_input.readonly) && $_input.readonly} readonly="readonly"{/if}
                                            {if isset($_input.disabled) && $_input.disabled} disabled="disabled"{/if}
                                            {if isset($_input.autocomplete) && !$_input.autocomplete} autocomplete="off"{/if}
                                            {if isset($_input.required) && $_input.required} required="required" {/if}
                                            {if isset($_input.placeholder) && $_input.placeholder} placeholder="{$_input.placeholder}"{/if} />
		                                    {if isset($_input.suffix)}
												<span class="input-group-addon">
													{$_input.suffix}
												</span>
		                                    {/if}

	                                {if isset($_input.maxchar) || isset($_input.prefix) || isset($_input.suffix)}
										</div>
	                                {/if}

                                    {if $languages|count > 1}
										</div>
										<div class="col-lg-3">
											<div class="bm-flex">
											<button type="button"
											        class="btn btn-default dropdown-toggle"
											        tabindex="-1"
											        data-toggle="dropdown">
                                                {$language.iso_code}
												<i class="icon-caret-down"></i>
											</button>

											<ul class="dropdown-menu">
                                                {foreach from=$languages item=language}
													<li>
														<a href="javascript:hideOtherLanguage({$language.id_lang});"
														   tabindex="-1">{$language.name}</a>
													</li>
                                                {/foreach}
											</ul>

                                            {if isset($_input.modal) && !isset($_input.class)}
	                                            <a target="#" data-toggle="modal"
	                                               data-target="#{$_input.modal}" style="cursor:pointer">
													<img width="22" style="margin-left: 6px;"
													     class="bm-info--small__icon img-fluid"
													     src="{$src_img|escape:'html':'UTF-8'}/question.png"
													/>
	                                            </a>
                                            {/if}

											</div>


										</div>
                                        {if isset($_input.help)}
											<div class="col-lg-12">
												<p class="help-text">{$_input.help}</p>
											</div>
                                        {/if}
										</div>

                                    {/if}
                                {/foreach}

                                {if $languages|count > 1}
									</div>
                                {/if}
                            {else}

                                {assign var='value_text' value=$fields_value[$_input.name]}
                                {if isset($_input.maxchar) || isset($_input.prefix) || isset($_input.suffix)}
									<div class="input-group{if isset($_input.class)} {$_input.class}{/if}">
                                {/if}

                                {if isset($_input.prefix)}
									<span class="input-group-addon">
										  {$_input.prefix}
										</span>
                                {/if}
								<input type="text"
								       name="{$_input.name}"
								       id="{if isset($_input.id)}{$_input.id}{else}{$_input.name}{/if}"
								       value="{if isset($_input.string_format) && $_input.string_format}{if isset($value_text) && !empty($value_text)}{$value_text|string_format:$_input.string_format|escape:'html':'UTF-8'}{else}{if isset($_input.default_val) && !empty($_input.default_val)}{$_input.default_val|string_format:$_input.string_format|escape:'html':'UTF-8'}{/if}{/if}{else}{if isset($value_text) && !empty($value_text)}{$value_text|escape:'html':'UTF-8'}{else}{if isset($_input.default_val) && !empty($_input.default_val)}{$_input.default_val|escape:'html':'UTF-8'}{/if}{/if}{/if}"
								       class="{if isset($_input.class)}{$_input.class}{/if}"
                                        {if isset($_input.size)} size="{$_input.size}"{/if}
                                        {if isset($_input.maxchar)} data-maxchar="{$_input.maxchar}"{/if}
                                        {if isset($_input.maxlength)} maxlength="{$_input.maxlength}"{/if}
                                        {if isset($_input.readonly) && $_input.readonly} readonly="readonly"{/if}
                                        {if isset($_input.disabled) && $_input.disabled} disabled="disabled"{/if}
                                        {if isset($_input.autocomplete) && !$_input.autocomplete} autocomplete="off"{/if}
                                        {if isset($_input.required) && $_input.required } required="required" {/if}
                                        {if isset($_input.placeholder) && $_input.placeholder } placeholder="{$_input.placeholder}"{/if}
								/>

                                {if isset($_input.suffix)}
									<span class="input-group-addon">
										  {$_input.suffix}
										</span>
                                {/if}

                                {if isset($_input.help)}
									<p class="help-text">{$_input.help}</p>
                                {/if}

                                {if isset($_input.maxchar) || isset($_input.prefix) || isset($_input.suffix)}
									</div>
                                {/if}

                            {/if}


                        {elseif $_input.type == 'select'}
                            {if isset($_input.options.query) && !$_input.options.query && isset($_input.empty_message)}
                                {$_input.empty_message}
                                {$_input.required = false}
                                {$_input.desc = null}
                            {else}
								<select name="{$_input.name|escape:'html':'UTF-8'}"
								        class="{if isset($_input.class)}{$_input.class|escape:'html':'UTF-8'}{/if} bm-select"
								        id="{if isset($_input.id)}{$_input.id|escape:'html':'UTF-8'}{else}{$_input.name|escape:'html':'UTF-8'}{/if}"
                                        {if isset($_input.multiple)}multiple="multiple" {/if}
                                        {if isset($_input.size)}size="{$_input.size|escape:'html':'UTF-8'}"{/if}
                                        {if isset($_input.onchange)}onchange="{$_input.onchange|escape:'html':'UTF-8'}"{/if}>
                                    {if isset($_input.options.default)}
										<option value="{$_input.options.default.value|escape:'html':'UTF-8'}">{$_input.options.default.label|escape:'html':'UTF-8'}</option>
                                    {/if}
                                    {if isset($_input.options.optiongroup)}
                                        {foreach $_input.options.optiongroup.query AS $optiongroup}
											<optgroup
													label="{$optiongroup[$_input.options.optiongroup.label]}">
                                                {foreach $optiongroup[$_input.options.options.query] as $option}
													<option value="{$option[$_input.options.options.id]}"
                                                            {if isset($_input.multiple)}
                                                                {foreach $fields_value[$_input.name] as $field_value}
                                                                    {if $field_value == $option[$_input.options.options.id]}selected="selected"{/if}
                                                                {/foreach}
                                                            {else}
                                                                {if $fields_value[$_input.name] == $option[$_input.options.options.id]}selected="selected"{/if}
                                                            {/if}
													>{$option[$_input.options.options.name]}</option>
                                                {/foreach}
											</optgroup>
                                        {/foreach}
                                    {else}
                                        {foreach $_input.options.query AS $option}
                                            {if is_object($option)}
												<option value="{$option->$_input.options.id}"
                                                        {if isset($_input.multiple)}
                                                            {foreach $fields_value[$_input.name] as $field_value}
                                                                {if $field_value == $option->$_input.options.id}
																	selected="selected"
                                                                {/if}
                                                            {/foreach}
                                                        {else}
                                                            {if $fields_value[$_input.name] == $option->$_input.options.id}
																selected="selected"
                                                            {/if}
                                                        {/if}
												>{$option->$_input.options.name}</option>
                                            {elseif $option == "-"}
												<option value="">-</option>
                                            {else}
												<option value="{$option[$_input.options.id]}"
                                                        {if isset($_input.multiple)}
                                                            {foreach $fields_value[$_input.name] as $field_value}
                                                                {if $field_value == $option[$_input.options.id]}
																	selected="selected"
                                                                {/if}
                                                            {/foreach}
                                                        {else}
                                                            {if $fields_value[$_input.name] == $option[$_input.options.id]}
																selected="selected"
                                                            {/if}
                                                        {/if}
												>{$option[$_input.options.name]}</option>
                                            {/if}
                                        {/foreach}
                                    {/if}
								</select>
                            {/if}
                        {elseif $_input.type == 'radio'}
                            {foreach $_input.values as $value}
								<div class="radio {if isset($_input.class)}{$_input.class}{/if}">
                                    {strip}
										<label>
											<input type="radio"
											       name="{$_input.name}"
											       id="{$value.id}"
											       value="{$value.value|escape:'html':'UTF-8'}"{if $fields_value[$_input.name] == $value.value} checked="checked"{/if}{if isset($_input.disabled) && $_input.disabled} disabled="disabled"{/if}/>
                                            {$value.label}
										</label>
                                    {/strip}
								</div>
                                {if isset($value.p) && $value.p}<p
										class="help-block">{$value.p}</p>{/if}
                            {/foreach}

                        {elseif $_input.type == 'switch' || $_input.type == 'shop' || $_input.type == 'switch-choose' }
	                        <div class="bm-flex {if isset($_input.class)}bm-offset-3{/if}">
							<span class="bm-switch fixed-width-lg {if $_input.type == 'switch-choose'}bm-switch--choose{/if}"

                                {if isset($_input.size) && $_input.size == 'auto'}
                                    style="width: 350px !important;"
                                {/if}

                                {if isset($_input.size) && $_input.size == 'full'}
                                    {if isset($_input.modal)}
	                                    style="width: calc(100% - 45px) !important;"
	                                    {else}
	                                    style="width: 100% !important;"
                                    {/if}

                                {/if}

                                >
								{foreach $_input.values as $value}
									<input type="radio" name="{$_input.name}"
									{if $value.value == 1}
										id="{$_input.name}_on"
                                    {else}
										id="{$_input.name}_off"
                                    {/if}
									value="{$value.value}"
									{if $fields_value[$_input.name] == $value.value}
										checked="checked"
                                    {/if}
                                    {if isset($_input.disabled) && $_input.disabled || isset($_input.class) && $_input.class == 'bm-no-active'}
										disabled="disabled"
                                    {/if}
									/>
									{strip}
										<label {if isset($_input.modal) && isset($_input.class) && $_input.class == 'bm-no-active'}data-modal="{$_input.modal}"{/if} {if $value.value == 1}
										for="{$_input.name}_on"{else}for="{$_input.name}_off"{/if}>
											{if $value.value == 1}{$value.label}{else}{$value.label}{/if}
										</label>
	                                {/strip}
                                {/foreach}
								<a class="slide-button btn"></a>
								</span>
	                            {if isset($_input.modal) && !isset($_input.class)}
			                        <a target="#" data-toggle="modal"
			                           data-target="#{$_input.modal}" style="cursor:pointer">
				                        <img width="22" style="margin-left: 6px;"
				                             class="bm-info--small__icon img-fluid"
				                             src="{$src_img|escape:'html':'UTF-8'}/question.png"
				                        />
			                        </a>
	                            {/if}
	                        </div>


                            {if isset($_input.help)}
								<p class="help-text">{$_input.help}</p>
                            {/if}


                        {elseif $_input.type == 'textarea'}
                            {assign var=use_textarea_autosize value=true}
                            {if isset($_input.lang) AND $_input.lang}
                                {foreach $languages as $language}
                                    {if $languages|count > 1}
										<div class="form-group translatable-field lang-{$language.id_lang}"{if $language.id_lang != $defaultFormLanguage} style="display:none;"{/if}>
										<div class="col-lg-3">
                                    {/if}
									<textarea
											name="{$_input.name}_{$language.id_lang}"
											class="{if isset($_input.autoload_rte) && $_input.autoload_rte}rte autoload_rte{if isset($_input.class)} {$_input.class}{/if}{else}{if isset($_input.class)} {$_input.class}{else} textarea-autosize{/if}{/if}">{$fields_value[$_input.name][$language.id_lang]|escape:'html':'UTF-8'}</textarea>
                                    {if $languages|count > 1}
										</div>
										<div class="col-lg-2">
											<button type="button"
											        class="btn btn-default dropdown-toggle"
											        tabindex="-1"
											        data-toggle="dropdown">
                                                {$language.iso_code}
												<span class="caret"></span>
											</button>
											<ul class="dropdown-menu">
                                                {foreach from=$languages item=language}
													<li>
														<a href="javascript:hideOtherLanguage({$language.id_lang});"
														   tabindex="-1">{$language.name}</a>
													</li>
                                                {/foreach}
											</ul>
										</div>
										</div>
                                    {/if}
                                {/foreach}

                            {else}
								<textarea name="{$_input.name}"
								          id="{if isset($_input.id)}{$_input.id}{else}{$_input.name}{/if}"
                                          {if isset($_input.cols)}cols="{$_input.cols}"{/if} {if isset($_input.rows)}rows="{$_input.rows}"{/if} class="{if isset($_input.autoload_rte) && $_input.autoload_rte}rte autoload_rte{if isset($_input.class)} {$_input.class}{/if}{else} textarea-autosize{/if}">{$fields_value[$_input.name]|escape:'html':'UTF-8'}</textarea>
                            {/if}
                        {elseif $_input.type == 'checkbox'}
                            {if isset($_input.expand)}
								<a class="btn btn-default show_checkbox{if strtolower($_input.expand.default) == 'hide'} hidden{/if}"
								   href="#">
									<i class="icon-{$_input.expand.show.icon}"></i>
                                    {$_input.expand.show.text}
                                    {if isset($_input.expand.print_total) && $_input.expand.print_total > 0}
										<span class="badge">{$_input.expand.print_total}</span>
                                    {/if}
								</a>
								<a class="btn btn-default hide_checkbox{if strtolower($_input.expand.default) == 'show'} hidden{/if}"
								   href="#">
									<i class="icon-{$_input.expand.hide.icon}"></i>
                                    {$_input.expand.hide.text}
                                    {if isset($_input.expand.print_total) && $_input.expand.print_total > 0}
										<span class="badge">{$_input.expand.print_total}</span>
                                    {/if}
								</a>
                            {/if}
                            {foreach $_input.values.query as $value}
                                {assign var=id_checkbox value=$_input.name|cat:'_'|cat:$value[$_input.values.id]}
								<div class="checkbox{if isset($_input.expand) && strtolower($_input.expand.default) == 'show'} hidden{/if}">
                                    {strip}
										<label for="{$id_checkbox}">
											<input type="checkbox"
											       name="{$id_checkbox}"
											       id="{$id_checkbox}"
											       class="{if isset($_input.class)}{$_input.class}{/if}"{if isset($value.val)} value="{$value.val|escape:'html':'UTF-8'}"{/if}{if isset($fields_value[$id_checkbox]) && $fields_value[$id_checkbox]} checked="checked"{/if} />
                                            {$value[$_input.values.name]}
										</label>
                                    {/strip}
								</div>
                            {/foreach}
                        {elseif $_input.type == 'group'}
                            {assign var=groups value=$_input.values}
                            {include file='helpers/form/form_group.tpl'}
                        {elseif $_input.type == 'html'}
                            {if isset($_input.html_content)}
                                {$_input.html_content}
                            {else}
                                {$_input.name}
                            {/if}
                        {/if}
                    {/block}

                    {block name="description"}
                        {if isset($_input.desc) && !empty($_input.desc)}
							<p class="help-block">
                                {if is_array($_input.desc)}
                                    {foreach $_input.desc as $p}
                                        {if is_array($p)}
											<span id="{$p.id}">{$p.text}</span>
											<br/>
                                        {else}
                                            {$p}
											<br/>
                                        {/if}
                                    {/foreach}
                                {else}
                                    {$_input.desc}
                                {/if}
							</p>
                        {/if}
                    {/block}

				</div>
            {/block}
        {/if}
	</div>
{/block}
