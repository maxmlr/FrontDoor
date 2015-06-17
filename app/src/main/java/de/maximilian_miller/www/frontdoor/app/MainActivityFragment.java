package de.maximilian_miller.www.frontdoor.app;

import android.content.Intent;
import android.content.SharedPreferences;
import android.os.AsyncTask;
import android.preference.PreferenceManager;
import android.support.v4.app.Fragment;
import android.os.Bundle;
import android.util.Log;
import android.view.Gravity;
import android.view.LayoutInflater;
import android.view.Menu;
import android.view.MenuInflater;
import android.view.MenuItem;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Button;
import android.widget.EditText;
import android.widget.ProgressBar;
import android.widget.Toast;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.BufferedInputStream;
import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.PrintWriter;
import java.io.UnsupportedEncodingException;
import java.net.HttpURLConnection;
import java.net.MalformedURLException;
import java.net.URL;
import java.net.URLEncoder;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.Iterator;
import java.util.List;
import java.util.Map;


/**
 * Main Screen
 */
public class MainActivityFragment extends Fragment implements View.OnClickListener
{
    private EditText panel;
    private Button btn0;
    private Button btn1;
    private Button btn2;
    private Button btn3;
    private Button btn4;
    private Button btn5;
    private Button btn6;
    private Button btn7;
    private Button btn8;
    private Button btn9;
    private Button btnCancel;
    private Button btnOK;
    private ProgressBar pb;

    private boolean straightExecution = false;

    public static final int RESPONSE_OK = 0;
    public static final int RESPONSE_ERROR = 1;

    public MainActivityFragment() {
    }

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setHasOptionsMenu(true);
        // Get the message from the intent
        Intent intent = this.getActivity().getIntent();
        if (intent.hasExtra(NFCActivity.NFC_TAG)) {
            String nfc_tag = intent.getStringExtra(NFCActivity.NFC_TAG);
            straightExecution = true;
        }
    }

    @Override
    public void onActivityCreated(Bundle savedInstanceState) {
        super.onActivityCreated(savedInstanceState);
        Log.d("FrontDoor:HttpRequest", "Straight execution: "+straightExecution);
        if (straightExecution) {
            SharedPreferences sp = PreferenceManager.getDefaultSharedPreferences(getActivity());
            if (panel.getText().toString().isEmpty())
            {
                Toast toast = Toast.makeText(getActivity().getApplicationContext(), R.string.msg_no_key, Toast.LENGTH_LONG);
                toast.setGravity(Gravity.CENTER, Gravity.CENTER, Gravity.CENTER);
                toast.show();
                return;
            }
            MyAsyncTask task = new MyAsyncTask();
            task.execute(panel.getText().toString());
        }
    }

    @Override
    public void onCreateOptionsMenu(Menu menu, MenuInflater inflater)
    {
        inflater.inflate(R.menu.menu_main, menu);
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        switch (item.getItemId()) {
            case R.id.action_settings:
                startActivity(new Intent(this.getActivity(), SettingsActivity.class));
                return true;
            case R.id.action_nfc:
                startActivity(new Intent(this.getActivity(), NFCActivity.class));
                return true;

        }

        return super.onOptionsItemSelected(item);
    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState)
    {
        SharedPreferences sPrefs = PreferenceManager.getDefaultSharedPreferences(getActivity());
        String preferenceRememberKey = getString(R.string.preference_remember_key);
        String preferenceKeyKey = getString(R.string.preference_key_key);
        boolean remembered = sPrefs.getBoolean(preferenceRememberKey, false);
        String rememberedCode = sPrefs.getString(preferenceKeyKey,"");

        View inflatedView = inflater.inflate(R.layout.fragment_main, container, false);
        panel =(EditText)inflatedView.findViewById(R.id.key);
        if (remembered && rememberedCode != null && !rememberedCode.isEmpty())
            panel.setText(rememberedCode);
        btn0=(Button)inflatedView.findViewById(R.id.btn0);
        btn0=(Button)inflatedView.findViewById(R.id.btn0);
        btn1=(Button)inflatedView.findViewById(R.id.btn1);
        btn2=(Button)inflatedView.findViewById(R.id.btn2);
        btn3=(Button)inflatedView.findViewById(R.id.btn3);
        btn4=(Button)inflatedView.findViewById(R.id.btn4);
        btn5=(Button)inflatedView.findViewById(R.id.btn5);
        btn6=(Button)inflatedView.findViewById(R.id.btn6);
        btn7=(Button)inflatedView.findViewById(R.id.btn7);
        btn8=(Button)inflatedView.findViewById(R.id.btn8);
        btn9=(Button)inflatedView.findViewById(R.id.btn9);
        btnOK=(Button)inflatedView.findViewById(R.id.buttonOK);
        btnCancel=(Button)inflatedView.findViewById(R.id.buttonCancel);
        pb=(ProgressBar)inflatedView.findViewById(R.id.progressBar1);
        pb.setVisibility(View.INVISIBLE);
        btn0.setOnClickListener(this);
        btn1.setOnClickListener(this);
        btn2.setOnClickListener(this);
        btn3.setOnClickListener(this);
        btn4.setOnClickListener(this);
        btn5.setOnClickListener(this);
        btn6.setOnClickListener(this);
        btn7.setOnClickListener(this);
        btn8.setOnClickListener(this);
        btn9.setOnClickListener(this);
        btnCancel.setOnClickListener(this);
        btnOK.setOnClickListener(this);
        return inflatedView;
    }

    @Override
    public void onClick(View v)
    {
        switch(v.getId())
        {
            case R.id.btn0:
                panel.setText(panel.getText()+"0");
                break;
            case R.id.btn1:
                panel.setText(panel.getText()+"1");
                break;
            case R.id.btn2:
                panel.setText(panel.getText()+"2");
                break;
            case R.id.btn3:
                panel.setText(panel.getText()+"3");
                break;
            case R.id.btn4:
                panel.setText(panel.getText()+"4");
                break;
            case R.id.btn5:
                panel.setText(panel.getText()+"5");
                break;
            case R.id.btn6:
                panel.setText(panel.getText()+"6");
                break;
            case R.id.btn7:
                panel.setText(panel.getText()+"7");
                break;
            case R.id.btn8:
                panel.setText(panel.getText()+"8");
                break;
            case R.id.btn9:
                panel.setText(panel.getText()+"9");
                break;
            case R.id.buttonCancel:
                panel.setText("");
                panel.setHint(getString(R.string.hint));
                break;
            case R.id.buttonOK:
                SharedPreferences sp = PreferenceManager.getDefaultSharedPreferences(getActivity());
                boolean remember = sp.getBoolean(getString(R.string.preference_remember_key), false);
                if (remember)
                {
                    SharedPreferences.Editor editor = sp.edit();
                    editor.putString(getString(R.string.preference_key_key), panel.getText().toString());
                    editor.commit();
                }
                MyAsyncTask task = new MyAsyncTask();
                task.execute(panel.getText().toString());
        }
    }

    private class MyAsyncTask extends AsyncTask<String, Integer, HashMap<String, Object>>
    {
        String code;

        @Override
        protected HashMap<String, Object> doInBackground(String... params)
        {
            Map<String, String> parameters = new HashMap<>();
            parameters.put("key",params[0]);
            return postData(createQueryStringForParameters(parameters));
        }

        @Override
        protected void onPreExecute()
        {
            code = panel.getText().toString();
            panel.setText("");
            panel.setHint("");
            pb.setVisibility(View.VISIBLE);
        }

        @Override
        protected void onPostExecute(HashMap<String, Object> response)
        {
            int responseCode = (int)response.get("response_code");
            pb.setVisibility(View.INVISIBLE);
            String preferenceRememberKey = getString(R.string.preference_remember_key);
            SharedPreferences sp = PreferenceManager.getDefaultSharedPreferences(getActivity());
            boolean remembered = sp.getBoolean(preferenceRememberKey, false);
            if (!remembered)
                panel.setHint(R.string.hint);
            else
                panel.setText(code);
            String message;
            if (responseCode == RESPONSE_OK)
            {
                if ((int)response.get("valid") == 1)
                    message = "Welcome back " + response.get("name") + "!";
                else
                    message = "DENIED";
            }
            else
                message = "ERROR: " + response.get("msg_err");
            Toast toast = Toast.makeText(getActivity().getApplicationContext(), message, Toast.LENGTH_LONG);
            toast.setGravity(Gravity.CENTER, Gravity.CENTER, Gravity.CENTER);
            toast.show();
        }

        @Override
        protected void onProgressUpdate(Integer... progress)
        {
            pb.setProgress(progress[0]);
        }

        public HashMap<String, Object> postData(String postParameters)
        {
            HashMap<String, Object> response = new HashMap<>();
            BufferedReader br = null;
            HttpURLConnection conn = null;

            SharedPreferences sPrefs = PreferenceManager.getDefaultSharedPreferences(getActivity());
            String preferenceUrlKey = getString(R.string.preference_url_key);
            String preferenceUrlDefault = getString(R.string.preference_url_default);
            String preferences_url = sPrefs.getString(preferenceUrlKey,preferenceUrlDefault);

            try {
                URL url = new URL(preferences_url);

                conn = (HttpURLConnection) url.openConnection();

                conn.setRequestMethod("POST");
                conn.setReadTimeout(10000);
                conn.setConnectTimeout(15000);
                conn.setDoInput(true);
                conn.setDoOutput(true);
                conn.setRequestProperty("Content-Type",
                        "application/x-www-form-urlencoded");
                conn.setFixedLengthStreamingMode(
                        postParameters.getBytes().length);

                //send the POST out
                PrintWriter out;
                try {
                    out = new PrintWriter(conn.getOutputStream());
                    out.print(postParameters);
                    out.close();
                } catch (IOException e) {
                    e.printStackTrace();
                }

                int responseCode = RESPONSE_OK;

                // handle issues
                int statusCode;

                statusCode = conn.getResponseCode();

                if (statusCode != HttpURLConnection.HTTP_OK) {
                    responseCode = RESPONSE_ERROR;
                    response.put("msg_err", "Connection failure: "+responseCode);
                }
                else
                {
                    // read the response
                    String response_raw = "";

                    InputStream in = new BufferedInputStream(conn.getInputStream());

                    br = new BufferedReader(new InputStreamReader(in));

                    String line;
                    while ((line = br.readLine()) != null) {
                        response_raw += line + "\n";
                    }
                    JSONObject json = new JSONObject(response_raw.trim());
                    response.putAll(jsonToMap(json));
                }
                response.put("response_code", responseCode);


            } catch (MalformedURLException e) {
                response.put("msg_err", e.getMessage());
                Log.e("FrontDoor:HttpRequest", e.getMessage(), e);
            } catch (IOException e1) {
                response.put("msg_err", e1.getMessage());
                Log.e("FrontDoor:HttpRequest", e1.getMessage(), e1);
            } catch (JSONException e2) {
                response.put("msg_err", e2.getMessage());
                Log.e("FrontDoor:HttpRequest", e2.getMessage(), e2);
            } finally {
                if (conn != null) {
                    conn.disconnect();
                }
                if (br != null) {
                    try {
                        br.close();
                    } catch (final IOException e) {
                        Log.e("FrontDoor:HttpRequest", e.getMessage(), e);
                    }
                }

            }

            return response;
        }

        // create post query string
        private static final char PARAMETER_DELIMITER = '&';
        private static final char PARAMETER_EQUALS_CHAR = '=';
        public String createQueryStringForParameters(Map<String, String> parameters)
        {
            StringBuilder parametersAsQueryString = new StringBuilder();
            try {
                if (parameters != null) {
                    boolean firstParameter = true;

                    for (String parameterName : parameters.keySet()) {
                        if (!firstParameter) {
                            parametersAsQueryString.append(PARAMETER_DELIMITER);
                        }

                        parametersAsQueryString.append(parameterName)
                                .append(PARAMETER_EQUALS_CHAR)
                                .append(URLEncoder.encode(
                                        parameters.get(parameterName), "UTF-8"));

                        firstParameter = false;
                    }
                }
            } catch (UnsupportedEncodingException e)
            {
                // catch exception
            }
            return parametersAsQueryString.toString();
        }

        // convert inputstream to String
        private String convertInputStreamToString(InputStream inputStream) throws IOException
        {
            BufferedReader bufferedReader = new BufferedReader( new InputStreamReader(inputStream));
            String line;
            String result = "";
            while((line = bufferedReader.readLine()) != null)
                result += line;

            inputStream.close();
            return result;

        }

        public Map<String, Object> jsonToMap(JSONObject json) throws JSONException {
            Map<String, Object> retMap = new HashMap<>();

            if(json != JSONObject.NULL) {
                retMap = toMap(json);
            }
            return retMap;
        }

        public Map<String, Object> toMap(JSONObject object) throws JSONException {
            Map<String, Object> map = new HashMap<>();

            Iterator<String> keysItr = object.keys();
            while(keysItr.hasNext()) {
                String key = keysItr.next();
                Object value = object.get(key);

                if(value instanceof JSONArray) {
                    value = toList((JSONArray) value);
                }

                else if(value instanceof JSONObject) {
                    value = toMap((JSONObject) value);
                }
                map.put(key, value);
            }
            return map;
        }

        public List<Object> toList(JSONArray array) throws JSONException {
            List<Object> list = new ArrayList<>();
            for(int i = 0; i < array.length(); i++) {
                Object value = array.get(i);
                if(value instanceof JSONArray) {
                    value = toList((JSONArray) value);
                }

                else if(value instanceof JSONObject) {
                    value = toMap((JSONObject) value);
                }
                list.add(value);
            }
            return list;
        }
    }
}
