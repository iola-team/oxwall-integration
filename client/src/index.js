/*
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

import '@iola/elements';
import { Settings } from './pages';

/**
 * Permalink assets
 */
import '!!file-loader?name=default-background.jpg!./assets/background.jpg';
import '!!file-loader?name=default-logo.png!./assets/logo.png';

/**
 * Pages
 */
export const pages = {
  Settings,
};
