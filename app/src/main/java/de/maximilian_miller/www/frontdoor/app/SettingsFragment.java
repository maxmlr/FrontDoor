package de.maximilian_miller.www.frontdoor.app;

import android.content.SharedPreferences;
import android.content.SharedPreferences.OnSharedPreferenceChangeListener;
import android.os.Bundle;
import android.preference.CheckBoxPreference;
import android.preference.EditTextPreference;
import android.preference.Preference;
import android.preference.PreferenceFragment;

public class SettingsFragment extends PreferenceFragment implements OnSharedPreferenceChangeListener
{
    @Override
    public void onCreate(Bundle savedInstanceState)
    {
        super.onCreate(savedInstanceState);
        addPreferencesFromResource(R.xml.user_settings);

        SharedPreferences sp = getPreferenceScreen().getSharedPreferences();

        EditTextPreference urlPref = (EditTextPreference) findPreference(getString(R.string.preference_url_key));
        urlPref
                .setSummary(sp.getString(getString(R.string.preference_url_key), ""));

        EditTextPreference keyPref = (EditTextPreference) findPreference(getString(R.string.preference_key_key));
        keyPref
                .setSummary(keyPref.getEditText().getTransformationMethod().getTransformation(keyPref.getText(), this.getView()));

        CheckBoxPreference checkBoxPref = (CheckBoxPreference) findPreference(getString(R.string.preference_remember_key));
        if (!checkBoxPref.isChecked())
            keyPref.setEnabled(false);
    }

    @Override
    public void onSharedPreferenceChanged(SharedPreferences sharedPreferences, String key)
    {
        Preference pref = findPreference(key);
        if (pref instanceof EditTextPreference) {
            EditTextPreference etp = (EditTextPreference) pref;
            if (pref.getKey().equals(getString(R.string.preference_key_key)))
                pref.setSummary(etp.getEditText().getTransformationMethod().getTransformation(etp.getText(), this.getView()));
            else if (pref.getKey().equals(getString(R.string.preference_url_key)))
                pref.setSummary(etp.getText());
        }
        else if (pref instanceof CheckBoxPreference) {
            if (pref.getKey().equals(getString(R.string.preference_remember_key)))
            {
                CheckBoxPreference cbp = (CheckBoxPreference) pref;
                EditTextPreference etp = (EditTextPreference) findPreference(getString(R.string.preference_key_key));
                if (cbp.isChecked())
                    etp.setEnabled(true);
                else
                {
                    SharedPreferences sp = getPreferenceScreen().getSharedPreferences();
                    SharedPreferences.Editor editor = sp.edit();
                    editor.putString(getString(R.string.preference_key_key), "");
                    editor.commit();
                    etp.setText("");
                    etp.setSummary("");
                    etp.setEnabled(false);
                }
            }
        }
    }

    @Override
    public void onResume() {
        super.onResume();
        getPreferenceScreen().getSharedPreferences()
                .registerOnSharedPreferenceChangeListener(this);
    }

    @Override
    public void onPause() {
        super.onPause();
        getPreferenceScreen().getSharedPreferences()
                .unregisterOnSharedPreferenceChangeListener(this);
    }
}
