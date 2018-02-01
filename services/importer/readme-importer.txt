Il flusso di import prevede: 
-- Uno scheduler applicativo, lato Sysdata, che rilascia i file per l'upload ("reportEsitoLead_uploading.csv") 
   nella cartella: 
/var/wind/w3b/report/merged/
   Lo scheduler applicativo poi preleva i file al percorso precedente ed effettua un upload su Eloqua.

# Per abilitare lo scheduler applicativo, accedere via putty/GnuNano al file degli scheduler Linux, 
  all'host di produzione, 52.212.54.111, con il comando "crontab -e", decommentare l'ultima riga, e 
  inserire i giorni e l'orario per la frequenza di schedulazione decisa:
-- # DISABILITATO SCHEDULER IMPORTER: */2 * * * * php /var/www/html/MailRestService/importer_launcher.php



-- DEPRECATED: 
-- Per ora non viene usato lo scheduler Eloqua per l'upload file csv (la funzione è integrata nello scheduler applicativo)
-- nel caso tornasse utile riattivarlo, ecco alcune note:
-- Uno scheduler Eloqua potrebbe, se attivato, prelevare i file al percorso:
/var/wind/w3b/report/merged/
   ed effettuare un upload su Eloqua. Lo scheduler necessariamente prende in ingresso un nome fisso, pari a:
/var/wind/w3b/report/merged/reportEsitoLead_uploading.csv
   lo scheduler Eloqua "WIND Contact Import - W3B_Report_Esito" è definito in:
https://secure.p06.eloqua.com/Main.aspx#data_managment
# Nel caso si volesse abilitare lo scheduler WIND Contact Import - W3B_Report_Esito: 
-- Inserire i giorni e l'orario per la frequenza di schedulazione decisa 
-- Inserire una email di gruppo per evidenziare gli import in errore (ora c'è francesco.imperato@sysdata.it)
