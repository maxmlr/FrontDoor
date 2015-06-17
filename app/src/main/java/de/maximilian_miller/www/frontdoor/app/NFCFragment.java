package de.maximilian_miller.www.frontdoor.app;

import android.app.PendingIntent;
import android.content.Intent;
import android.content.IntentFilter;
import android.content.SharedPreferences;
import android.nfc.NdefMessage;
import android.nfc.NdefRecord;
import android.nfc.NfcAdapter;
import android.nfc.Tag;
import android.nfc.tech.Ndef;
import android.nfc.tech.NdefFormatable;
import android.os.Bundle;
import android.preference.EditTextPreference;
import android.preference.PreferenceManager;
import android.support.v4.app.Fragment;
import android.support.v7.app.AppCompatActivity;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Button;
import android.widget.EditText;
import android.widget.TextView;
import android.widget.Toast;

import java.io.IOException;
import java.io.UnsupportedEncodingException;
import java.nio.charset.Charset;
import java.util.Arrays;


public class NFCFragment extends Fragment implements View.OnClickListener, NFCActivity.NCFListener
{
    private NfcAdapter mAdapter;
    private AppCompatActivity activity;
    private boolean mInWriteMode;
    private Button mWriteTagButton;
    private EditText mTagText;
    private TextView mTextView;

    public boolean ismInWriteMode() {
        return mInWriteMode;
    }

    public void setmInWriteMode(boolean mInWriteMode) {
        this.mInWriteMode = mInWriteMode;
    }

    protected NfcAdapter getAdapter()
    {
        return mAdapter;
    }

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        // grab our NFC Adapter
        mAdapter = NfcAdapter.getDefaultAdapter(getActivity());

        if (mAdapter == null)
            Toast.makeText(getActivity(), "This device doesn't support NFC.", Toast.LENGTH_LONG).show();
    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        View inflatedView = inflater.inflate(R.layout.fragment_nfc, container, false);

        SharedPreferences sPrefs = PreferenceManager.getDefaultSharedPreferences(getActivity());
        String currentUrlKey = getString(R.string.preference_url_key);
        String url = sPrefs.getString(currentUrlKey, "");

        mTagText = (EditText) inflatedView.findViewById(R.id.write_tag_text);
        mWriteTagButton = (Button) inflatedView.findViewById(R.id.write_tag_button);
        mTextView = (TextView) inflatedView.findViewById(R.id.text_view);
        mTagText.setText(url);
        //mTagText.setEnabled(false);
        mWriteTagButton.setOnClickListener(this);

        if (!mAdapter.isEnabled()) {
            mTextView.setText("NFC is disabled.");
        } else {
            mTextView.setText("NFC is enabled.");
        }

        return inflatedView;
    }

    protected String readTag(Tag tag, String action, String type)
    {
        if (NfcAdapter.ACTION_NDEF_DISCOVERED.equals(action)) {

            if (getString(R.string.mimetype_nfc).equals(type)) {

                NdefReader reader = new NdefReader(tag);
                String content = reader.read();
                Log.i("FrontDoor:NFC", "Tag content: " + content);
                return content;

            } else {
                Log.d("FrontDoor:NFC", "Wrong mime type: " + type);
            }
        } else if (NfcAdapter.ACTION_TECH_DISCOVERED.equals(action)) {

            // In case we would still use the Tech Discovered Intent
            String[] techList = tag.getTechList();
            String searchedTech = Ndef.class.getName();

            for (String tech : techList) {
                if (searchedTech.equals(tech)) {
                    NdefReader reader = new NdefReader(tag);
                    String content = reader.read();
                    Log.i("FrontDoor:NFC", "Tag content: " + content);
                    return content;
                }
            }
        }
        return null;
    }

    /**
     * Format a tag and write our NDEF message
     */
    protected boolean writeTag(Tag tag) {
        // record to launch Play Store if app is not installed
        NdefRecord appRecord = NdefRecord.createApplicationRecord(getActivity().getApplicationContext().getPackageName());

        // record that contains our custom "retro console" game data, using custom MIME_TYPE
        //("US-ASCII"));
        //byte[] payload = mTagText.getText().toString().getBytes();
        //byte[] mimeBytes = getString(R.string.mimetype_nfc).getBytes(Charset.forName("UTF-8"));
        //NdefRecord cardRecord = new NdefRecord(NdefRecord.TNF_MIME_MEDIA, mimeBytes,
        //        new byte[0], payload);
        NdefRecord doorRecord = createRecord(mTagText.getText().toString(), "en");
        NdefMessage message = new NdefMessage(new NdefRecord[] { doorRecord, appRecord});

        try {
            // see if tag is already NDEF formatted
            Ndef ndef = Ndef.get(tag);
            if (ndef != null) {
                ndef.connect();

                if (!ndef.isWritable()) {
                    displayMessage("Read-only tag.");
                    return false;
                }

                // work out how much space we need for the data
                int size = message.toByteArray().length;
                if (ndef.getMaxSize() < size) {
                    displayMessage("Tag doesn't have enough free space.");
                    return false;
                }

                ndef.writeNdefMessage(message);
                ndef.close();
                displayMessage("Tag written successfully.");
                return true;
            } else {
                // attempt to format tag
                NdefFormatable format = NdefFormatable.get(tag);
                if (format != null) {
                    try {
                        format.connect();
                        format.format(message);
                        displayMessage("Tag written successfully!\nClose this app and scan tag.");
                        return true;
                    } catch (IOException e) {
                        displayMessage("Unable to format tag to NDEF.");
                        return false;
                    }
                } else {
                    displayMessage("Tag doesn't appear to support NDEF format.");
                    return false;
                }
            }
        } catch (Exception e) {
            displayMessage("Failed to write tag");
            Log.e("FrontDoor:NFC", "Failed to write tag", e);
        }

        return false;
    }

    private void displayMessage(String message) {
        mTextView.setText(message);
    }

    public void onClick(View v) {
        if(v.getId() == R.id.write_tag_button) {
            displayMessage("Touch and hold tag against phone to write.");
            enableWriteMode();
        }
    }

    protected void disableWriteMode() {
        mAdapter.disableForegroundDispatch(getActivity());
    }

    @Override
    public void onPause() {
        super.onPause();
        disableWriteMode();
    }

    @Override
    public void setWriteMode(boolean active) {
        mInWriteMode = active;
    }

    @Override
    public boolean isWriteEnabled() {
        return mInWriteMode;
    }

    @Override
    public void setActvity(AppCompatActivity activity) {
        this.activity = activity;
    }

    @Override
    public void writeNCF(Tag tag) {
        writeTag(tag);
    }

    @Override
    public String readNCF(Tag tag, String action, String type) {
        return readTag(tag, action, type);
    }

    /**
     * Force this Activity to get NFC events first
     */
    public void enableWriteMode() {
        setmInWriteMode(true);

        // set up a PendingIntent to open the app when a tag is scanned
        PendingIntent pendingIntent = PendingIntent.getActivity(activity, 0,
                new Intent(activity, activity.getClass()).addFlags(Intent.FLAG_ACTIVITY_SINGLE_TOP), 0);
        IntentFilter tagDetected = new IntentFilter(NfcAdapter.ACTION_TAG_DISCOVERED);
        IntentFilter[] filters = new IntentFilter[] { tagDetected };

        mAdapter.enableForegroundDispatch(activity, pendingIntent, filters, null);
    }

    /**
     * Background task for reading the data. Do not block the UI thread while reading.
     */
    private class NdefReader
    {
        Tag tag;

        public NdefReader(Tag tag)
        {
            this.tag = tag;
        }

        protected String read() {
            Ndef ndef = Ndef.get(tag);
            if (ndef == null) {
                // NDEF is not supported by this Tag.
                return null;
            }

            NdefMessage ndefMessage = ndef.getCachedNdefMessage();

            NdefRecord[] records = ndefMessage.getRecords();
            for (NdefRecord ndefRecord : records) {
                if (ndefRecord.getTnf() == NdefRecord.TNF_MIME_MEDIA && Arrays.equals(ndefRecord.getType(), getString(R.string.mimetype_nfc).getBytes())) {
                    try {
                        return readText(ndefRecord);
                    } catch (UnsupportedEncodingException e) {
                        Log.e("FrontDoor:NFC", "Unsupported Encoding", e);
                    }
                }
            }

            return null;
        }

        private String readText(NdefRecord record) throws UnsupportedEncodingException {
        /*
         * See NFC forum specification for "Text Record Type Definition" at 3.2.1
         *
         * http://www.nfc-forum.org/specs/
         *
         * bit_7 defines encoding
         * bit_6 reserved for future use, must be 0
         * bit_5..0 length of IANA language code
         */

            byte[] payload = record.getPayload();

            // Get the Text Encoding
            String textEncoding = ((payload[0] & 128) == 0) ? (String)"UTF-8" : (String)"UTF-16";

            // Get the Language Code
            int languageCodeLength = payload[0] & 0063;

            // String languageCode = new String(payload, 1, languageCodeLength, "US-ASCII");
            // e.g. "en"

            // Get the Text
            return new String(payload, languageCodeLength + 1, payload.length - languageCodeLength - 1, textEncoding);
        }
    }

    private NdefRecord createRecord(String text, String lang)
    {
        byte[] textBytes  = text.getBytes();
        byte[] langBytes  = new byte[0];
        byte[] mimeBytes = getString(R.string.mimetype_nfc).getBytes(Charset.forName("UTF-8"));
        try {
            langBytes = lang.getBytes("UTF-8");
        } catch (UnsupportedEncodingException e) {
            Log.e("FrontDoor:NFC", "Unsupported Encoding", e);
        }
        int    langLength = langBytes.length;
        int    textLength = textBytes.length;
        byte[] payload    = new byte[1 + langLength + textLength];

        // set status byte (see NDEF spec for actual bits)
        payload[0] = (byte) langLength;

        // copy langbytes and textbytes into payload
        System.arraycopy(langBytes, 0, payload, 1,              langLength);
        System.arraycopy(textBytes, 0, payload, 1 + langLength, textLength);

        NdefRecord record = new NdefRecord(NdefRecord.TNF_MIME_MEDIA, mimeBytes,
                new byte[0],
                payload);

        return record;
    }
}
