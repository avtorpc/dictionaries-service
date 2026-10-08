"""Check the live reference API after the dictionary migrations."""
import json, urllib.request, urllib.error
BASE='http://localhost:8080/api/refbook/'
expected={'app-settings':8,'countries':3,'roles':2,'specializations':120,'grades':3,'competencies':65,'technologies':62,'specialization-competencies':1543,'criteria':72,'criterion-competencies':78}
def get(name):
 with urllib.request.urlopen(BASE+name,timeout=20) as response:
  assert response.status==200
  body=json.load(response)
  assert body['success'],body
  return body['data']
assert set(get('list')['items'])==set(expected),'Unexpected dictionary list'
for name,count in expected.items():
 data=get(name)
 assert data['total']==count and len(data['items'])==count,(name,data['total'])
 assert get(name)==data,(name,'Cache changed response')
 if name=='criteria':
  for item in data['items']:
   assert isinstance(item['check_templates'],list) and isinstance(item['version'],int) and isinstance(item['active'],bool),item['code']
 if name=='roles':assert {item['code'] for item in data['items']}=={'applicant','employer'}
 if name=='grades':assert [item['code'] for item in data['items']]==['junior','middle','senior']
 print(name+': '+str(count)+' items, API and cached response passed',flush=True)
for name in ['roles-countries','communication-channels-countries','communication-channels','positions','verification-statuses','legal-forms','document-types']:
 try:
  urllib.request.urlopen(BASE+name,timeout=20)
  raise AssertionError('Removed dictionary is still available: '+name)
 except urllib.error.HTTPError as error:
  assert error.code==404,(name,error.code)
print('Removed dictionary APIs return 404',flush=True)
