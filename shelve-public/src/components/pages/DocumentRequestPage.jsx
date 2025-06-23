import React, { useState, useEffect } from 'react';
import { useLocation, useNavigate, Link } from 'react-router-dom';
import { documentApi } from '../../services/documentApi';
import Loading from '../common/Loading';
import { validateEmail, validatePhone } from '../../utils/validators';

const DocumentRequestPage = () => {
  const location = useLocation();
  const navigate = useNavigate();
  const [formData, setFormData] = useState({
    // Informations personnelles
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    address: '',
    city: '',
    postal_code: '',
    country: 'France',

    // Statut du demandeur
    status: 'individual', // individual, researcher, student, professional
    institution: '',
    research_subject: '',

    // Détails de la demande
    request_type: 'consultation', // consultation, reproduction, loan
    documents: [], // Documents demandés
    consultation_date: '',
    consultation_time: '',
    purpose: '',
    description: '',

    // Conditions
    terms_accepted: false,
    data_processing_accepted: false
  });

  const [errors, setErrors] = useState({});
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [submitSuccess, setSubmitSuccess] = useState(false);
  // Récupération des informations du document si transmises
  useEffect(() => {
    if (location.state?.recordId) {
      setFormData(prev => ({
        ...prev,
        documents: [{
          id: location.state.recordId,
          title: location.state.recordTitle || 'Document sélectionné',
          type: 'consultation'
        }]
      }));
    }
  }, [location.state]);

  const handleInputChange = (field, value) => {
    setFormData(prev => ({ ...prev, [field]: value }));

    // Effacer l'erreur si le champ est corrigé
    if (errors[field]) {
      setErrors(prev => ({ ...prev, [field]: null }));
    }
  };

  const handleAddDocument = () => {
    const newDocument = {
      id: Date.now(), // ID temporaire
      title: '',
      reference: '',
      type: 'consultation'
    };

    setFormData(prev => ({
      ...prev,
      documents: [...prev.documents, newDocument]
    }));
  };

  const handleRemoveDocument = (index) => {
    setFormData(prev => ({
      ...prev,
      documents: prev.documents.filter((_, i) => i !== index)
    }));
  };

  const handleDocumentChange = (index, field, value) => {
    setFormData(prev => ({
      ...prev,
      documents: prev.documents.map((doc, i) =>
        i === index ? { ...doc, [field]: value } : doc
      )
    }));
  };
  const validateBasicFields = () => {
    const newErrors = {};

    if (!formData.first_name.trim()) newErrors.first_name = 'Le prénom est obligatoire';
    if (!formData.last_name.trim()) newErrors.last_name = 'Le nom est obligatoire';
    if (!formData.email.trim()) {
      newErrors.email = 'L\'email est obligatoire';
    } else if (!validateEmail(formData.email)) {
      newErrors.email = 'Format d\'email invalide';
    }

    if (formData.phone && !validatePhone(formData.phone)) {
      newErrors.phone = 'Format de téléphone invalide';
    }

    if (!formData.purpose.trim()) newErrors.purpose = 'Le motif de la demande est obligatoire';

    return newErrors;
  };

  const validateConsultationFields = () => {
    const newErrors = {};

    if (formData.request_type === 'consultation') {
      if (!formData.consultation_date) newErrors.consultation_date = 'La date de consultation est obligatoire';
      if (!formData.consultation_time) newErrors.consultation_time = 'L\'horaire de consultation est obligatoire';
    }

    return newErrors;
  };

  const validateDocuments = () => {
    const newErrors = {};

    if (formData.documents.length === 0) {
      newErrors.documents = 'Veuillez spécifier au moins un document';
    } else {
      formData.documents.forEach((doc, index) => {
        if (!doc.title.trim() && !doc.reference.trim()) {
          newErrors[`document_${index}`] = 'Titre ou référence obligatoire';
        }
      });
    }

    return newErrors;
  };

  const validateTerms = () => {
    const newErrors = {};

    if (!formData.terms_accepted) {
      newErrors.terms_accepted = 'Vous devez accepter les conditions d\'utilisation';
    }

    if (!formData.data_processing_accepted) {
      newErrors.data_processing_accepted = 'Vous devez accepter le traitement des données';
    }

    return newErrors;
  };

  const validateForm = () => {
    const errors = {
      ...validateBasicFields(),
      ...validateConsultationFields(),
      ...validateDocuments(),
      ...validateTerms()
    };

    setErrors(errors);
    return Object.keys(errors).length === 0;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    if (!validateForm()) {
      return;
    }

    setIsSubmitting(true);

    try {
      await documentApi.submitRequest(formData);
      setSubmitSuccess(true);

      // Redirection après succès
      setTimeout(() => {
        navigate('/', {
          state: {
            message: 'Votre demande a été envoyée avec succès. Vous recevrez une confirmation par email.'
          }
        });
      }, 3000);

    } catch (error) {
      setErrors({
        submit: error.message || 'Une erreur est survenue lors de l\'envoi de la demande'
      });
    } finally {
      setIsSubmitting(false);
    }
  };

  if (submitSuccess) {
    return (
      <div className="container mx-auto px-4 py-8">
        <div className="max-w-md mx-auto text-center">
          <div className="text-green-500 text-6xl mb-4">✓</div>
          <h1 className="text-2xl font-bold text-gray-900 mb-4">
            Demande envoyée avec succès !
          </h1>
          <p className="text-gray-600 mb-6">
            Votre demande de consultation a été transmise à nos équipes.
            Vous recevrez une confirmation par email dans les plus brefs délais.
          </p>
          <Link
            to="/"
            className="inline-block bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700"
          >
            Retour à l'accueil
          </Link>
        </div>
      </div>
    );
  }

  return (
    <div className="document-request-page">
      <div className="container mx-auto px-4 py-8">
        {/* En-tête */}
        <div className="page-header mb-8">
          <h1 className="text-3xl font-bold text-gray-900 mb-4">
            Demande de consultation de documents
          </h1>
          <p className="text-lg text-gray-600 mb-6">
            Remplissez ce formulaire pour demander la consultation de documents d'archives
          </p>

          <nav className="breadcrumb" aria-label="Fil d'Ariane">
            <ol className="flex items-center space-x-2 text-sm text-gray-600">
              <li>
                <Link to="/" className="hover:text-blue-600">Accueil</Link>
              </li>
              <li className="before:content-['/'] before:mx-2">
                Demande de consultation
              </li>
            </ol>
          </nav>
        </div>

        <form onSubmit={handleSubmit} className="max-w-4xl mx-auto">
          {/* Informations personnelles */}
          <div className="form-section bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 className="text-xl font-semibold text-gray-900 mb-6">
              Informations personnelles
            </h2>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label htmlFor="first_name" className="block text-sm font-medium text-gray-700 mb-2">
                  Prénom <span className="text-red-500">*</span>
                </label>
                <input
                  type="text"
                  id="first_name"
                  value={formData.first_name}
                  onChange={(e) => handleInputChange('first_name', e.target.value)}
                  className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 ${
                    errors.first_name ? 'border-red-500' : 'border-gray-300'
                  }`}
                />
                {errors.first_name && (
                  <p className="text-red-500 text-sm mt-1">{errors.first_name}</p>
                )}
              </div>

              <div>
                <label htmlFor="last_name" className="block text-sm font-medium text-gray-700 mb-2">
                  Nom <span className="text-red-500">*</span>
                </label>
                <input
                  type="text"
                  id="last_name"
                  value={formData.last_name}
                  onChange={(e) => handleInputChange('last_name', e.target.value)}
                  className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 ${
                    errors.last_name ? 'border-red-500' : 'border-gray-300'
                  }`}
                />
                {errors.last_name && (
                  <p className="text-red-500 text-sm mt-1">{errors.last_name}</p>
                )}
              </div>

              <div>
                <label htmlFor="email" className="block text-sm font-medium text-gray-700 mb-2">
                  Email <span className="text-red-500">*</span>
                </label>
                <input
                  type="email"
                  id="email"
                  value={formData.email}
                  onChange={(e) => handleInputChange('email', e.target.value)}
                  className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 ${
                    errors.email ? 'border-red-500' : 'border-gray-300'
                  }`}
                />
                {errors.email && (
                  <p className="text-red-500 text-sm mt-1">{errors.email}</p>
                )}
              </div>

              <div>
                <label htmlFor="phone" className="block text-sm font-medium text-gray-700 mb-2">
                  Téléphone
                </label>
                <input
                  type="tel"
                  id="phone"
                  value={formData.phone}
                  onChange={(e) => handleInputChange('phone', e.target.value)}
                  className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 ${
                    errors.phone ? 'border-red-500' : 'border-gray-300'
                  }`}
                />
                {errors.phone && (
                  <p className="text-red-500 text-sm mt-1">{errors.phone}</p>
                )}
              </div>

              <div className="md:col-span-2">
                <label htmlFor="address" className="block text-sm font-medium text-gray-700 mb-2">
                  Adresse
                </label>
                <input
                  type="text"
                  id="address"
                  value={formData.address}
                  onChange={(e) => handleInputChange('address', e.target.value)}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <div>
                <label htmlFor="city" className="block text-sm font-medium text-gray-700 mb-2">
                  Ville
                </label>
                <input
                  type="text"
                  id="city"
                  value={formData.city}
                  onChange={(e) => handleInputChange('city', e.target.value)}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <div>
                <label htmlFor="postal_code" className="block text-sm font-medium text-gray-700 mb-2">
                  Code postal
                </label>
                <input
                  type="text"
                  id="postal_code"
                  value={formData.postal_code}
                  onChange={(e) => handleInputChange('postal_code', e.target.value)}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
            </div>
          </div>

          {/* Statut du demandeur */}
          <div className="form-section bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 className="text-xl font-semibold text-gray-900 mb-6">
              Statut du demandeur
            </h2>            <div className="space-y-4">
              <fieldset>
                <legend className="block text-sm font-medium text-gray-700 mb-3">
                  Vous êtes :
                </legend>
                <div className="space-y-2">
                  {[
                    { value: 'individual', label: 'Particulier' },
                    { value: 'researcher', label: 'Chercheur' },
                    { value: 'student', label: 'Étudiant' },
                    { value: 'professional', label: 'Professionnel' }
                  ].map(option => (
                    <label key={option.value} className="flex items-center">
                      <input
                        type="radio"
                        name="status"
                        value={option.value}
                        checked={formData.status === option.value}
                        onChange={(e) => handleInputChange('status', e.target.value)}
                        className="mr-2"
                      />
                      {option.label}
                    </label>
                  ))}
                </div>
              </fieldset>

              {(formData.status === 'researcher' || formData.status === 'student' || formData.status === 'professional') && (
                <div>
                  <label htmlFor="institution" className="block text-sm font-medium text-gray-700 mb-2">
                    Institution / Organisme
                  </label>
                  <input
                    type="text"
                    id="institution"
                    value={formData.institution}
                    onChange={(e) => handleInputChange('institution', e.target.value)}
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>
              )}

              {formData.status === 'researcher' && (
                <div>
                  <label htmlFor="research_subject" className="block text-sm font-medium text-gray-700 mb-2">
                    Sujet de recherche
                  </label>
                  <textarea
                    id="research_subject"
                    rows="3"
                    value={formData.research_subject}
                    onChange={(e) => handleInputChange('research_subject', e.target.value)}
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>
              )}
            </div>
          </div>

          {/* Documents demandés */}
          <div className="form-section bg-white rounded-lg shadow-md p-6 mb-8">
            <div className="flex justify-between items-center mb-6">
              <h2 className="text-xl font-semibold text-gray-900">
                Documents demandés
              </h2>
              <button
                type="button"
                onClick={handleAddDocument}
                className="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm"
              >
                Ajouter un document
              </button>
            </div>

            {formData.documents.length === 0 ? (
              <p className="text-gray-500 text-center py-8">
                Aucun document spécifié. Cliquez sur "Ajouter un document" pour commencer.
              </p>
            ) : (
              <div className="space-y-4">                {formData.documents.map((document, index) => (
                  <div key={`document-${index}-${document.id || 'new'}`} className="border border-gray-200 rounded-lg p-4">
                    <div className="flex justify-between items-start mb-4">
                      <h3 className="font-medium text-gray-900">Document {index + 1}</h3>
                      <button
                        type="button"
                        onClick={() => handleRemoveDocument(index)}
                        className="text-red-600 hover:text-red-800 text-sm"
                      >
                        Supprimer
                      </button>
                    </div>
                      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <div>
                        <label htmlFor={`document-title-${index}`} className="block text-sm font-medium text-gray-700 mb-2">
                          Titre du document
                        </label>
                        <input
                          type="text"
                          id={`document-title-${index}`}
                          value={document.title}
                          onChange={(e) => handleDocumentChange(index, 'title', e.target.value)}
                          className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="Titre ou description du document"
                        />
                      </div>

                      <div>
                        <label htmlFor={`document-reference-${index}`} className="block text-sm font-medium text-gray-700 mb-2">
                          Référence / Cote
                        </label>
                        <input
                          type="text"
                          id={`document-reference-${index}`}
                          value={document.reference || ''}
                          onChange={(e) => handleDocumentChange(index, 'reference', e.target.value)}
                          className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="Référence archivistique"
                        />
                      </div>
                    </div>

                    {errors[`document_${index}`] && (
                      <p className="text-red-500 text-sm mt-2">{errors[`document_${index}`]}</p>
                    )}
                  </div>
                ))}
              </div>
            )}

            {errors.documents && (
              <p className="text-red-500 text-sm mt-4">{errors.documents}</p>
            )}
          </div>

          {/* Détails de la consultation */}
          <div className="form-section bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 className="text-xl font-semibold text-gray-900 mb-6">
              Détails de la consultation
            </h2>
              <div className="space-y-6">
              <fieldset>
                <legend className="block text-sm font-medium text-gray-700 mb-3">
                  Type de demande :
                </legend>
                <div className="space-y-2">
                  {[
                    { value: 'consultation', label: 'Consultation sur place' },
                    { value: 'reproduction', label: 'Demande de reproduction' },
                    { value: 'loan', label: 'Prêt (si autorisé)' }
                  ].map(option => (
                    <label key={option.value} className="flex items-center">
                      <input
                        type="radio"
                        name="request_type"
                        value={option.value}
                        checked={formData.request_type === option.value}
                        onChange={(e) => handleInputChange('request_type', e.target.value)}
                        className="mr-2"
                      />
                      {option.label}
                    </label>
                  ))}
                </div>
              </fieldset>

              {formData.request_type === 'consultation' && (
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <label htmlFor="consultation_date" className="block text-sm font-medium text-gray-700 mb-2">
                      Date souhaitée <span className="text-red-500">*</span>
                    </label>
                    <input
                      type="date"
                      id="consultation_date"
                      value={formData.consultation_date}
                      onChange={(e) => handleInputChange('consultation_date', e.target.value)}
                      min={new Date().toISOString().split('T')[0]}
                      className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 ${
                        errors.consultation_date ? 'border-red-500' : 'border-gray-300'
                      }`}
                    />
                    {errors.consultation_date && (
                      <p className="text-red-500 text-sm mt-1">{errors.consultation_date}</p>
                    )}
                  </div>

                  <div>
                    <label htmlFor="consultation_time" className="block text-sm font-medium text-gray-700 mb-2">
                      Horaire souhaité <span className="text-red-500">*</span>
                    </label>
                    <select
                      id="consultation_time"
                      value={formData.consultation_time}
                      onChange={(e) => handleInputChange('consultation_time', e.target.value)}
                      className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 ${
                        errors.consultation_time ? 'border-red-500' : 'border-gray-300'
                      }`}
                    >
                      <option value="">Sélectionner un horaire</option>
                      <option value="09:00">09h00 - 12h00</option>
                      <option value="14:00">14h00 - 17h00</option>
                      <option value="full_day">Journée complète</option>
                    </select>
                    {errors.consultation_time && (
                      <p className="text-red-500 text-sm mt-1">{errors.consultation_time}</p>
                    )}
                  </div>
                </div>
              )}

              <div>
                <label htmlFor="purpose" className="block text-sm font-medium text-gray-700 mb-2">
                  Motif de la demande <span className="text-red-500">*</span>
                </label>
                <textarea
                  id="purpose"
                  rows="3"
                  value={formData.purpose}
                  onChange={(e) => handleInputChange('purpose', e.target.value)}
                  placeholder="Précisez l'objet de votre recherche et l'utilisation prévue des documents"
                  className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 ${
                    errors.purpose ? 'border-red-500' : 'border-gray-300'
                  }`}
                />
                {errors.purpose && (
                  <p className="text-red-500 text-sm mt-1">{errors.purpose}</p>
                )}
              </div>

              <div>
                <label htmlFor="description" className="block text-sm font-medium text-gray-700 mb-2">
                  Informations complémentaires
                </label>
                <textarea
                  id="description"
                  rows="4"
                  value={formData.description}
                  onChange={(e) => handleInputChange('description', e.target.value)}
                  placeholder="Toute information utile pour traiter votre demande"
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
            </div>
          </div>

          {/* Conditions */}
          <div className="form-section bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 className="text-xl font-semibold text-gray-900 mb-6">
              Conditions d'utilisation
            </h2>

            <div className="space-y-4">
              <label className="flex items-start">
                <input
                  type="checkbox"
                  checked={formData.terms_accepted}
                  onChange={(e) => handleInputChange('terms_accepted', e.target.checked)}
                  className="mt-1 mr-3"
                />
                <span className="text-sm text-gray-700">
                  J'accepte les <Link to="/terms" className="text-blue-600 hover:text-blue-800">conditions d'utilisation</Link> et
                  le <Link to="/privacy" className="text-blue-600 hover:text-blue-800">règlement de consultation</Link> des archives. <span className="text-red-500">*</span>
                </span>
              </label>
              {errors.terms_accepted && (
                <p className="text-red-500 text-sm">{errors.terms_accepted}</p>
              )}

              <label className="flex items-start">
                <input
                  type="checkbox"
                  checked={formData.data_processing_accepted}
                  onChange={(e) => handleInputChange('data_processing_accepted', e.target.checked)}
                  className="mt-1 mr-3"
                />
                <span className="text-sm text-gray-700">
                  J'accepte que mes données personnelles soient traitées dans le cadre de cette demande
                  conformément à la <Link to="/privacy" className="text-blue-600 hover:text-blue-800">politique de confidentialité</Link>. <span className="text-red-500">*</span>
                </span>
              </label>
              {errors.data_processing_accepted && (
                <p className="text-red-500 text-sm">{errors.data_processing_accepted}</p>
              )}
            </div>
          </div>

          {/* Erreur générale */}
          {errors.submit && (
            <div className="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
              <p className="text-red-700">{errors.submit}</p>
            </div>
          )}

          {/* Boutons d'action */}
          <div className="flex justify-between items-center">
            <button
              type="button"
              onClick={() => navigate(-1)}
              className="px-6 py-3 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              Annuler
            </button>

            <button
              type="submit"
              disabled={isSubmitting}
              className="px-8 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {isSubmitting ? (
                <>
                  <Loading className="inline w-4 h-4 mr-2" />
                  Envoi en cours...
                </>
              ) : (
                'Envoyer la demande'
              )}
            </button>
          </div>
        </form>

        {/* Informations complémentaires */}
        <div className="max-w-4xl mx-auto mt-12">
          <div className="bg-blue-50 rounded-lg p-6">
            <h3 className="text-lg font-semibold text-blue-900 mb-4">
              Informations importantes
            </h3>
            <ul className="space-y-2 text-blue-800 text-sm">
              <li>• Les consultations se font uniquement sur rendez-vous</li>
              <li>• Délai de traitement : 5 à 10 jours ouvrables</li>
              <li>• Une pièce d'identité sera demandée lors de la consultation</li>
              <li>• Certains documents peuvent nécessiter des conditions particulières de consultation</li>
              <li>• Pour toute question : archives@example.com ou 01 23 45 67 89</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  );
};

export default DocumentRequestPage;
