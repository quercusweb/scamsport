/*
 *                    ....
 *                  .:   '':.
 *                  ::::     ':..
 *                  ::.         ''..
 *       .:'.. ..':.:::'    . :.   '':.
 *      :.   ''     ''     '. ::::.. ..:
 *      ::::.        ..':.. .''':::::  .
 *      :::::::..    '..::::  :. ::::  :
 *      ::'':::::::.    ':::.'':.::::  :
 *      :..   ''::::::....':     ''::  :
 *      :::::.    ':::::   :     .. '' .
 *   .''::::::::... ':::.''   ..''  :.''''.
 *   :..:::'':::::  :::::...:''        :..:
 *   ::::::. '::::  ::::::::  ..::        .
 *   ::::::::.::::  ::::::::  :'':.::   .''
 *   ::: '::::::::.' '':::::  :.' '':  :
 *   :::   :::::::::..' ::::  ::...'   .
 *   :::  .::::::::::   ::::  ::::  .:'
 *    '::'  '':::::::   ::::  : ::  :
 *              '::::   ::::  :''  .:
 *               ::::   ::::    ..''
 *               :::: ..:::: .:''
 *                 ''''  '''''
 *
 *
 * AUTOMAD
 *
 * Copyright (c) 2021 by Marc Anton Dahmen
 * https://marcdahmen.de
 *
 * Licensed under the MIT license.
 */

import { App, classes, create, html, titleCase } from '../../core';
import {
	KeyValueMap,
	TemplateButtonStatus,
	TemplateFieldData,
	Theme,
} from '../../types';
import { BaseComponent } from '../Base';

/**
 * Beautify a template path to be used as name.
 *
 * @param template - the path of a given template
 * @param [themeName] - an optional theme name
 * @returns the beautified template name
 */
const templateName = (template: string, themeName: string = ''): string => {
	const templateName = template
		.split('/')
		.reverse()[0]
		.replace(/\.php$/g, '');

	return titleCase([themeName, templateName].join('/'));
};

/**
 * Simplify a template/theme path combination to represent a theme variable value.
 *
 * @param template - a template path
 * @param [path] - an optional path
 * @returns the template path
 */
const templatePath = (template: string, path: string = ''): string => {
	const templateName = template
		.split('/')
		.reverse()[0]
		.replace(/\.php$/g, '');

	return [path, templateName].filter((item) => item.length).join('/');
};

/**
 * Get all status info about the selected template.
 *
 * @param params
 * @param params.fields - the data object that was loaded from the page's data file
 * @param params.shared - the shared data object
 * @param params.template - a template path
 * @param params.themeKey - the field name for themes
 * @returns the UI items the represent a theme status
 */
const themeStatus = ({
	fields,
	template,
	themeKey,
}: TemplateFieldData): TemplateButtonStatus => {
	const themes = App.themes;
	let mainTheme = themes[App.mainTheme];

	if (typeof mainTheme == 'undefined') {
		mainTheme = Object.values(themes)[0];
	}

	let templateExists = false;
	let appliedTheme = mainTheme;
	let selectedTemplate = templatePath(template);
	let buttonLabel = titleCase(selectedTemplate);
	let buttonIcon = 'file-earmark-code';
	let buttonClass = 'success';

	if (typeof themes[fields[themeKey]] != 'undefined') {
		appliedTheme = themes[fields[themeKey]];
		buttonLabel = titleCase(`${appliedTheme.name}/${selectedTemplate}`);
		selectedTemplate = templatePath(template, appliedTheme.path);
	}

	templateExists = appliedTheme.templates.indexOf(template) !== -1;

	if (!templateExists) {
		buttonIcon = 'question-circle';
		buttonClass = 'danger';
	}

	return {
		buttonLabel,
		buttonClass,
		buttonIcon,
		selectedTemplate,
	};
};

/**
 * Create a set of options for the a optgroup.
 *
 * @param templates - the templates array
 * @param section - the section element
 * @param value - the currently used template
 * @param [themeName] - the optional theme name for the current group
 * @param [themePath] - the optional theme path
 */
const createOptions = (
	templates: string[],
	element: HTMLElement,
	value: string,
	themeName: string = '*',
	themePath: string = ''
): void => {
	templates.forEach((template) => {
		const file = templatePath(template, themePath);
		const option = create('option', [], { value: file }, element);

		option.textContent = templateName(file, themeName);

		if (value == file) {
			option.setAttribute('selected', '');
		}
	});
};

/**
 * Create a template select element.
 *
 * @param mainTheme
 * @param selectedTemplate
 * @returns the rendered element
 */
export const createTemplateSelect = (
	selectedTemplate: string
): HTMLSelectElement => {
	const mainTheme = App.themes[App.mainTheme];
	const themes = App.themes;
	const select = create('select', [classes.input], {
		name: 'theme_template',
	});
	const mainGroup = create('optgroup', [], { label: '*' }, select);

	createOptions(mainTheme.templates, mainGroup, selectedTemplate);

	Object.values(themes).forEach((theme: KeyValueMap) => {
		const group = create('optgroup', [], { label: theme.name }, select);

		createOptions(
			theme.templates,
			group,
			selectedTemplate,
			theme.name,
			theme.path
		);
	});

	return select;
};

/**
 * The template field button.
 *
 * @extends BaseComponent
 */
export class PageTemplateComponent extends BaseComponent {
	/**
	 * The field data.
	 *
	 * @param {KeyValueMap} params
	 * @param {KeyValueMap} params.fields
	 * @param {string} params.template
	 * @param {string} params.themeKey
	 */
	set data({ fields, template, themeKey }: TemplateFieldData) {
		this.render({ fields, template, themeKey });
	}

	/**
	 * The callback function used when an element is created in the DOM.
	 */
	connectedCallback(): void {
		this.classList.add(classes.field);
	}

	/**
	 * Render a template field button.
	 *
	 * @param params
	 * @param params.fields
	 * @param params.template
	 * @param params.themeKey
	 */
	private render({ fields, template, themeKey }: TemplateFieldData): void {
		const { buttonLabel, buttonIcon, buttonClass, selectedTemplate } =
			themeStatus({
				fields,
				template,
				themeKey,
			});

		const button = create('div', [classes.field], {}, this);
		const modal = create(
			'am-modal',
			[],
			{ id: 'am-page-template-modal' },
			this
		);
		const select = createTemplateSelect(selectedTemplate);

		button.innerHTML = html`
			<label class="${classes.fieldLabel}"
				>${App.text('page_theme_template')}</label
			>
			<am-modal-toggle
				modal="#am-page-template-modal"
				class="${classes.button} ${classes.button}--${buttonClass} ${classes.flex}"
			>
				<i class="bi bi-${buttonIcon}"></i>
				<span class="${classes.flexItemGrow}">${buttonLabel}</span>
				<i class="bi bi-pen"></i>
			</am-modal-toggle>
		`;

		modal.innerHTML = html`
			<div class="${classes.modalDialog}">
				<div class="${classes.modalHeader}">
					<span>${App.text('page_theme_template')}</span>
					<am-modal-close
						class="${classes.modalClose}"
					></am-modal-close>
				</div>
				${select.outerHTML}
				<div class="${classes.modalFooter}">
					<am-modal-close class="${classes.button}">
						${App.text('btn_close')}
					</am-modal-close>
					<am-submit
						class="${classes.button} ${classes.buttonSuccess}"
						form="Page/data"
					>
						${App.text('btn_apply_reload')}
					</am-submit>
				</div>
			</div>
		`;
	}
}

customElements.define('am-page-template', PageTemplateComponent);
